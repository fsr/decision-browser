import os

files = [
    os.path.join(dirpath, f)
    for dirpath, dirnames, files in os.walk("protokolle")
    for f in files
    if f.endswith(".tex")
]

q = "Lydia"

for file in files:
    # open file and check if q is in file
    with open(file, "r", encoding="utf-8") as f:
        try:
            for line in f.readlines():
                if q in line:
                    print(file)
                    print(line)
                    break
        except UnicodeDecodeError:
            # print("UnicodeDecodeError")
            continue
