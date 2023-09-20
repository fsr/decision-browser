from dataclasses import dataclass, asdict
from datetime import datetime
import os
from pprint import pp
import re
from sqlmodel import Field, Session, SQLModel, create_engine
from dotenv import load_dotenv

from models import Beschluss

load_dotenv()

db_url = os.getenv("DATABASE_URL")


def get_beschluesse(content, filename):
    # regex = r"^\s*\\begin{vote}[\s\S]*?\\voteMoney{(\d*)}[\s\S]*?\\voteNumber{(\d*)}[\s\S]*?\\voteText{(.*)}[\s\S]*?\\voting{(\d*)}{(\d*)}{(\d*)}[\s\S]*?\\end{vote}"

    # regex = r"^\s*\\begin{vote}[\s\S]*?\\voteMoney{(\d*)}[\s\S]*?\\voteNumber{(\d*)}[\s\S]*?\\voteText{(.*)}[\s\S]*?\\voteReason{(.*)}[\s\S]*?\\voting{(\d*)}{(\d*)}{(\d*)}[\s\S]*?\\end{vote}"

    regex = r"^\s*\\begin{vote}[\s\S]*?(?:\\voteMoney{(\d*)}[\s\S]*?)?\\voteNumber{(\d*)}[\s\S]*?\\voteText{(.*)}[\s\S]*?(?:\\voteSubmitter{(.*)}[\s\S]*?)?(?:\\voteReason{(.*)}[\s\S]*?)?\\voting{(\d*)}{(\d*)}{(\d*)}[\s\S]*?\\end{vote}"

    # regex = r"\\begin{vote}([\s\S]*?)\\end{vote}"
    print(filename)

    # turn filename into python date
    # like 2022-01-10.tex -> 2022-01-10

    # try to find \initVoteNumber{42} in file using regex
    # if found, use it as vote_number
    # else use 0
    vote_number = 0
    init_vote_number = re.search(r"\\initVoteNumber{(\d*)}", content)
    if init_vote_number:
        vote_number = int(init_vote_number.group(1))

    try:
        date = datetime.strptime(filename[-14:-4], "%Y-%m-%d")
    except ValueError:
        print("Invalid date in filename")
        return []

    # if year is before 2015, skip
    if date.year < 2008:
        return []

    # get all goups
    groups = re.findall(regex, content, re.MULTILINE)

    beschluesse = [
        Beschluss.from_tuple(group, date, i, vote_number)
        for i, group in enumerate(groups)
    ]

    return beschluesse


dir = "protokolle"

# find all tex recursively files in dir
files = [
    os.path.join(dirpath, f)
    for dirpath, dirnames, files in os.walk(dir)
    for f in files
    if f.endswith(".tex")
]

pp(files)
# exit
# import sys

# sys.exit()

# beschluesse = []
# for file in files:
#     with open(file, "r", encoding="utf-8") as f:
#         try:
#             content = f.read()
#         except UnicodeDecodeError:
#             print("UnicodeDecodeError")
#             continue
#         beschluesse += get_beschluesse(content, file)
# files = [f for f in os.listdir(dir) if f.endswith(".tex")]


engine = create_engine(db_url, echo=True)

# remove all tables
SQLModel.metadata.drop_all(engine)

SQLModel.metadata.create_all(engine)


all_beschluesse = []


for f in files:
    with open(f, "r", encoding="utf-8") as file:
        try:
            beschluesse = get_beschluesse(file.read(), f)
        except UnicodeDecodeError:
            print("UnicodeDecodeError")
            continue
        # if beschluesse list is empty, continue
        if not beschluesse:
            continue
        for beschluss in beschluesse:
            if beschluss.vote_text == "":
                continue

            all_beschluesse.append(beschluss)


# insert all_beschluesse into db
with Session(engine) as session:
    session.add_all(all_beschluesse)
    session.commit()
