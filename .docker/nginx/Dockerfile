FROM nginx:alpine

COPY ./nginx.conf /etc/nginx/conf.d/default.conf

RUN rm -rf /var/cache/apk/* && \
    rm -rf /tmp/*
