FROM devilbox/php-fpm:7.4-prod

RUN set -eux \
	&& DEBIAN_FRONTEND=noninteractive apt-get update -qq \
	&& DEBIAN_FRONTEND=noninteractive apt-get install -qq -y --no-install-recommends --no-install-suggests  poppler-utils

CMD ["php-fpm"]

EXPOSE 9000