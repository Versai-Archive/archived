# syntax = edrevo/dockerfile-plus
FROM ubuntu:rolling as Practice

INCLUDE+ ../../Setup.dockerfile

ADD plugins /home/plugins
ADD plugin_data /home/plugin_data

CMD [ "/home/start.sh" ]