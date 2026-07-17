self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    const targetUrl = event.notification.data?.url || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (windowClients) {
            const sameOriginClient = windowClients.find(function (client) {
                return new URL(client.url).origin === self.location.origin;
            });

            if (sameOriginClient) {
                return sameOriginClient.focus().then(function () {
                    return sameOriginClient.navigate(targetUrl);
                });
            }

            return clients.openWindow(targetUrl);
        })
    );
});
