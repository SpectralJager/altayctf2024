#!/bin/bash

piccolo migrations forwards app
uvicorn main:app --port 8000 --host 0.0.0.0
