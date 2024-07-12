FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

RUN apt update && \
    apt install git curl -y && \
    \
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install nodejs -y && \
    \
    apt clean && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY entrypoint.sh /app/entrypoint.sh

RUN chmod +x /app/entrypoint.sh

CMD ["/app/entrypoint.sh"]
