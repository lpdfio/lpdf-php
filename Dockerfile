FROM php:cli

# The same pinned runtime the workflows install, so a render that works here
# works in CI. wasmtime.dev/install.sh resolves "latest" through the
# unauthenticated GitHub API and exits 0 when that fails, installing nothing.
ARG WASMTIME_VERSION=v48.0.2

RUN apt-get update && apt-get install -y curl xz-utils unzip && \
    ARCHIVE="wasmtime-${WASMTIME_VERSION}-$(uname -m)-linux" && \
    curl -sSfL --retry 3 \
      "https://github.com/bytecodealliance/wasmtime/releases/download/${WASMTIME_VERSION}/${ARCHIVE}.tar.xz" \
      -o /tmp/wasmtime.tar.xz && \
    tar -xJf /tmp/wasmtime.tar.xz -C /usr/local/bin --strip-components=1 "${ARCHIVE}/wasmtime" && \
    rm /tmp/wasmtime.tar.xz && \
    wasmtime --version && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-interaction

COPY src/ ./src/
COPY test/ ./test/
COPY resources/ ./resources/
