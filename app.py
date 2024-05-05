from collections import defaultdict
from flask import Flask, request, jsonify, render_template, url_for, session, redirect
from sqlmodel import Field, Session, SQLModel, create_engine, select
import os
import re
from dotenv import load_dotenv
from datetime import datetime
from authlib.integrations.flask_client import OAuth 
from functools import wraps

from models import Beschluss

load_dotenv()

app = Flask(__name__)
app.secret_key = os.getenv("FLASK_SECRET")


oauth = OAuth(app)
oauth.register(
    name='dex',
    server_metadata_url=os.getenv("OAUTH_CONF_URL"),
    client_id=os.getenv("OAUTH_CLIENT_ID"),
    client_secret=os.getenv("OAUTH_CLIENT_SECRET"),
    client_kwargs={
        'scope': 'openid email profile'
    }
)


db_url = os.getenv("DATABASE_URL")

# fix server has gone away (ConnectionResetError(104, 'Connection reset by peer
engine = create_engine(db_url, echo=True, pool_pre_ping=True, pool_recycle=300)


def login_required(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if 'user' not in session:
            # Redirect to login page if user is not logged in
            return redirect(url_for('login'))
        return f(*args, **kwargs)
    return decorated_function

@app.route("/")
@login_required
def index():
    q = request.args.get("q")
    year = request.args.get("year") or str(datetime.now().year)

    # get all beschluesse withou id where vote_text it not empty and filter vote_text containing q if q is not None and sort by date
    with Session(engine) as session:
        beschluesse = (
            session.query(Beschluss)
            .filter(Beschluss.id != None)
            .filter(Beschluss.vote_text != "")
            .filter(Beschluss.vote_text.contains(q) if q else True)
            # only from given year if year is not None else 2022
            .filter(Beschluss.date.contains(year))
            # sort by date and id
            .order_by(Beschluss.date.desc(), Beschluss.id.desc())
            .all()
        )

        return render_template("index.html", beschluesse=beschluesse, q=q, year=year)


def grep_text(q: str):
    files = [
        os.path.join(dirpath, f)
        for dirpath, dirnames, files in os.walk("/protokolle")
        for f in files
        if f.endswith(".tex")
    ]

    # sort files by name
    files.sort(reverse=True)

    d = defaultdict(list)

    # print(files)

    for file in files:
        # open file and check if q is in file
        with open(file, "r", encoding="utf-8") as f:
            try:
                for line in f.readlines():
                    if q.lower() in line.lower():
                        # if line starts with % skip
                        if line.startswith("%"):
                            continue
                        d[file.split(".")[0]].append(line)
                        # print(file)
                        # print(line)
                        # break
            except UnicodeDecodeError:
                # print("UnicodeDecodeError")
                continue
    return d


@app.route("/grep")
@login_required
def grep():
    q = request.args.get("q")
    # check if q only contains letters using regex
    if q:
        # if not re.match(r"^[a-zA-Z]+$", q):
        #     return jsonify({"error": "q only contains letters"})
        results = grep_text(q)
        print(dict(results))

        def highlight(string):
            return re.sub(
                q,
                f"<span style='background-color: yellow;'>{q}</span>",
                string,
                flags=re.IGNORECASE,
            )

        # return string.replace(q, f"<span style='background-color: yellow;'>{q}</span>")

        return render_template("grep.html", results=results, q=q, highlight=highlight)

    return render_template("grep.html", q=q)


@app.route('/login')
def login():
    redirect_uri = url_for('auth', _external=True)
    return oauth.dex.authorize_redirect(redirect_uri)

@app.route('/auth')
def auth():
    token = oauth.dex.authorize_access_token()
    session['user'] = token['userinfo']
    return redirect('/')


@app.route('/logout')
def logout():
    session.pop('user', None)
    return redirect('/')

if __name__ == "__main__":
    if os.getenv("FLASK_ENV") == "development":
        app.run(port=5000, debug=True)
    else:
        app.run(host="127.0.0.1", port=5000)
