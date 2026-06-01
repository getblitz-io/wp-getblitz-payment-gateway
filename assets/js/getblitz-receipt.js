(function() {
    function showSoftError(message) {
        var statusEl = document.getElementById("getblitz-payment-status");
        var messageEl = document.getElementById("getblitz-status-message");
        var spinnerEl = document.querySelector("#getblitz-payment-status .getblitz-spinner");

        if (statusEl) {
            statusEl.classList.add("error");
            statusEl.style.display = "block";
        }

        if (messageEl) {
            messageEl.textContent = message;
        }

        if (spinnerEl) {
            spinnerEl.style.display = "none";
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        var vars = window.getblitzReceiptVars || {};
        var initGetBlitz = setInterval(function() {
            if (typeof GetBlitz === "undefined") {
                return;
            }
            clearInterval(initGetBlitz);

            var config = {
                sessionId: vars.sessionId,
                clientToken: vars.clientToken
            };

            if (vars.apiUrl) {
                config.apiUrl = vars.apiUrl;
            }

            if (vars.wssUrl) {
                config.wssUrl = vars.wssUrl;
            }

            var GetBlitzClass = (typeof GetBlitz.GetBlitz === "function") ? GetBlitz.GetBlitz : GetBlitz;
            var payment = new GetBlitzClass(config);

            payment.mount("#getblitz-payment-container").catch(function(err) {
                console.error("Failed to mount GetBlitz client:", err);
            });

            payment
                .on("onSuccess", function() {
                    var containerEl = document.getElementById("getblitz-payment-container");
                    var statusEl = document.getElementById("getblitz-payment-status");
                    var formData = new FormData();

                    if (containerEl) {
                        containerEl.style.display = "none";
                    }

                    if (statusEl) {
                        statusEl.style.display = "block";
                    }

                    formData.append("order_id", vars.orderId || "");
                    formData.append("session_id", vars.sessionId || "");
                    formData.append("getblitz_nonce", vars.nonce || "");

                    fetch(vars.verifyUrl, {
                        method: "POST",
                        body: formData
                    })
                        .then(function(response) {
                            return response.json();
                        })
                        .then(function(json) {
                            var redirectUrl = (json && json.data && json.data.redirect) ? json.data.redirect : null;
                            if (redirectUrl) {
                                window.location.href = redirectUrl;
                                return;
                            }
                            showSoftError(vars.softErrorText || "Payment received. Your order is being processed.");
                        })
                        .catch(function(err) {
                            console.error("GetBlitz verify error:", err);
                            showSoftError(vars.softErrorText || "Payment received. Your order is being processed.");
                        });
                })
                .on("onError", function(error) {
                    console.error("GetBlitz Payment Error:", error);
                })
                .on("onExpired", function() {
                    console.warn("GetBlitz Payment Session Expired");
                });
        }, 100);
    });
})();
