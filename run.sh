#!/bin/bash

poetry run gunicorn -w 1 --bind 127.0.0.1:5000 app:app