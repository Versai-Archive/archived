# This loads PMMP into a docker container
FROM ubuntu:rolling AS Setup

EXPOSE 19132/udp

RUN apt update && apt dist-upgrade -y && apt autoremove -y && apt install -y --no-install-recommends ca-certificates wget

WORKDIR /home

# RUN mkdir -p /home/practice && chown -R docker:docker /home/practice

ADD bin /home/bin
ADD Pocketmine-MP.phar /home/Pocketmine-MP.phar
ADD start.sh /home/start.sh
ADD pocketmine.yml /home/pocketmine.yml

# Add plugins?

RUN chmod o+x bin/php7/bin/php start.sh


ENV EXEC_ENV=DOCKER
ENV PRODUCTION=1