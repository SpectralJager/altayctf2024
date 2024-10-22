FROM python:3.11

RUN apt update

WORKDIR /opt/app

COPY requirements.txt .

RUN pip install \
    --no-cache-dir \
    --no-input \
    -r requirements.txt

COPY . .

ENV PYTHONUNBUFFERED=1
EXPOSE 8000
RUN chmod +x start.sh
CMD "/opt/app/start.sh"

