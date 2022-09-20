FROM python:3.8
RUN mkdir /app
ADD . /app
WORKDIR /app
RUN apt-get update; apt-get -y install git
RUN pip install -r requirements.txt
CMD ["python", "main.py"]

