import fs from 'node:fs';
import http from 'node:http';
import https from 'node:https';
import path from 'node:path';

const publicHost = process.env.HTTPS_HOST ?? 'msu-eva.test';
const listenHost = process.env.HTTPS_LISTEN_HOST ?? '127.0.0.1';
const port = Number(process.env.HTTPS_PORT ?? 8443);
const targetHost = process.env.TARGET_HOST ?? '127.0.0.1';
const targetPort = Number(process.env.TARGET_PORT ?? 8000);
const certificateDirectory = process.env.HTTPS_CERT_DIR ?? path.resolve('storage/uat-https');

const server = https.createServer(
    {
        cert: fs.readFileSync(path.join(certificateDirectory, 'localhost.pem')),
        key: fs.readFileSync(path.join(certificateDirectory, 'localhost-key.pem')),
    },
    (request, response) => {
        const headers = {
            ...request.headers,
            host: `${publicHost}:${port}`,
            'x-forwarded-host': `${publicHost}:${port}`,
            'x-forwarded-port': String(port),
            'x-forwarded-proto': 'https',
        };

        const proxyRequest = http.request(
            {
                host: targetHost,
                port: targetPort,
                method: request.method,
                path: request.url,
                headers,
            },
            (proxyResponse) => {
                response.writeHead(proxyResponse.statusCode ?? 502, proxyResponse.headers);
                proxyResponse.pipe(response);
            },
        );

        proxyRequest.on('error', (error) => {
            response.writeHead(502, { 'content-type': 'text/plain; charset=utf-8' });
            response.end(`HTTPS proxy could not reach Laravel: ${error.message}`);
        });

        request.pipe(proxyRequest);
    },
);

server.on('error', (error) => {
    if (error.code !== 'EADDRINUSE') {
        process.stderr.write(`Unable to start the local HTTPS proxy: ${error.message}\n`);
        process.exitCode = 1;
        return;
    }

    const healthRequest = https.get(
        {
            host: listenHost,
            port,
            path: '/login',
            rejectUnauthorized: false,
            timeout: 3000,
        },
        (response) => {
            response.resume();
            const certificate = response.socket.getPeerCertificate();
            const isLocalCertificate = certificate?.subject?.O === 'mkcert development certificate';

            if (isLocalCertificate) {
                process.stdout.write(`Local HTTPS proxy is already running at https://${publicHost}:${port}\n`);
                return;
            }

            process.stderr.write(`Port ${port} is already in use by another service.\n`);
            process.exitCode = 1;
        },
    );

    healthRequest.on('error', () => {
        process.stderr.write(`Port ${port} is already in use by another service.\n`);
        process.exitCode = 1;
    });

    healthRequest.on('timeout', () => {
        healthRequest.destroy();
    });
});

server.listen(port, listenHost, () => {
    process.stdout.write(`Local HTTPS proxy: https://${publicHost}:${port} -> http://${targetHost}:${targetPort}\n`);
});
