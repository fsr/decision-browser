import subprocess

cmd = "rsync -r matthias.stuhlbein@ifsr.de:/home/etherpad/protokolle/ protokolle"

subprocess.call(cmd, shell=True)

subprocess.call("python3 tex_to_db.py", shell=True)
