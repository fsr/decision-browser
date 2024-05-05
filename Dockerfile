FROM python:3.9-bullseye
COPY . /app
WORKDIR /app
RUN pip3 install -r requirements.txt
CMD ["gunicorn", "-w", "1", "--bind", "127.0.0.1:5055", "app:app"]
EXPOSE 5000/tcp
