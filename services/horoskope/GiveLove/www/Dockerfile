FROM node:20.10

RUN mkdir /www && chown -R node:node /www
WORKDIR /www
COPY --chown=node:node  . ./
#RUN rm -rf node_modules package-lock.json
RUN npm install
USER node
CMD npm start

