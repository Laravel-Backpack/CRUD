<script>
// Intercept XHR requests to check for exception responses
if (!XMLHttpRequest.prototype._backpackWrapped) {
    XMLHttpRequest.prototype._backpackWrapped = true;
    (function() {
        const originalOpen = XMLHttpRequest.prototype.open;
        const originalSend = XMLHttpRequest.prototype.send;
        const originalSetRequestHeader = XMLHttpRequest.prototype.setRequestHeader;

        XMLHttpRequest.prototype.open = function(method, url) {
            this._method = method;
            this._url = url;
            this._requestHeaders = {};
            return originalOpen.apply(this, arguments);
        };

        XMLHttpRequest.prototype.setRequestHeader = function(header, value) {
            this._requestHeaders[header] = value;
            return originalSetRequestHeader.apply(this, arguments);
        };

        XMLHttpRequest.prototype.send = function(body) {
            const self = this;

            this.addEventListener('load', function() {
                if (self._backpackRetry) {
                    if (typeof Noty !== 'undefined') Noty.closeAll();
                    showErrorFrame(self.responseText);
                    return;
                }

                try {
                    if (self.getResponseHeader('content-type')?.includes('application/json')) {
                        const response = JSON.parse(self.responseText);
                        if (response?.exception !== undefined) {
                            const retryXhr = new XMLHttpRequest();
                            retryXhr._backpackRetry = true;
                            retryXhr.open(self._method, self._url);
                            
                            Object.keys(self._requestHeaders).forEach(key => {
                                if (key.toLowerCase() !== 'accept') {
                                    retryXhr.setRequestHeader(key, self._requestHeaders[key]);
                                }
                            });
                            retryXhr.setRequestHeader('Accept', 'text/html');
                            retryXhr.send(body);
                        }
                    }
                } catch (e) {}
            });

            return originalSend.apply(this, arguments);
        };
    })();
}

const showErrorFrame = html => {
    let page = document.createElement('html');
    let isJson = false;
    let errorData = null;

    try {
        errorData = JSON.parse(html);
        isJson = typeof errorData === 'object' && errorData !== null && (errorData.exception || errorData.message);
    } catch (e) {
        isJson = false;
    }

    if (isJson) {
        page.innerHTML = `
            <head><meta charset="UTF-8"><\/head>
            <body>
                <div class="error-container">
                    <h2 class="error-title">${errorData.exception || 'Error'}</h2>
                    <div class="error-message">${errorData.message || 'Unknown error occurred'}</div>
                    ${errorData.file ? `<div class="error-file"><strong>File:</strong> ${errorData.file}:${errorData.line}</div>` : ''}
                    ${errorData.trace ? `
                        <details class="error-trace">
                            <summary>Stack Trace</summary>
                            <pre>${JSON.stringify(errorData.trace, null, 2)}</pre>
                        </details>
                    ` : ''}
                </div>
            <\/body>
        `;
    } else {
        page.innerHTML = html;
    }

    page.querySelectorAll('a').forEach(a => a.setAttribute('target', '_top'));

    let style = document.createElement('style');
    style.textContent = `
        html, body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 1rem;
            line-height: 1.5;
            color: #212529;
            background-color: #fff;
            margin: 0;
            padding: 0;
        }
        body {
            padding: 2rem;
        }
        h1, h2, h3, h4, h5, h6 {
            margin-top: 0;
            margin-bottom: 0.5rem;
            font-weight: 500;
            line-height: 1.2;
        }
        .error-container {
            background: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 1200px;
            margin: 0 auto;
        }
        .error-title {
            color: #dc3545;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 0.5rem;
        }
        .error-message {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            font-weight: bold;
            color: #212529;
        }
        .error-file {
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            word-break: break-all;
            font-size: 0.9rem;
            border: 1px solid #e9ecef;
        }
        .error-trace {
            margin-top: 1rem;
        }
        .error-trace summary {
            cursor: pointer;
            color: #0d6efd;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        .error-trace pre {
            background: #212529;
            color: #f8f9fa;
            padding: 1rem;
            border-radius: 4px;
            overflow-x: auto;
            font-size: 0.8rem;
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        }
        @media (prefers-color-scheme: dark) {
            html, body {
                color: #dee2e6;
                background-color: #212529;
            }
            a { color: #6ea8fe; }
            .error-container {
                background: #2c3035;
                box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            }
            .error-title {
                color: #ea868f;
                border-bottom-color: #495057;
            }
            .error-message {
                color: #dee2e6;
            }
            .error-file {
                background: #343a40;
                color: #e9ecef;
                border-color: #495057;
            }
            .error-trace pre {
                background: #000;
                border: 1px solid #495057;
            }
        }
    `;
    
    let head = page.querySelector('head');
    if (head) {
        head.prepend(style);
    } else {
        head = document.createElement('head');
        head.appendChild(style);
        page.prepend(head);
    }

    let modal = document.getElementById('ajax-error-frame');

    if (typeof modal !== 'undefined' && modal !== null) {
        modal.innerHTML = '';
    } else {
        modal = document.createElement('div');
        modal.id = 'ajax-error-frame';
        modal.style.position = 'fixed';
        modal.style.width = '100vw';
        modal.style.height = '100vh';
        modal.style.padding = '5vh 5vw';
        modal.style.backgroundColor = 'rgba(0, 0, 0, 0.4)';
        modal.style.zIndex = 200000;
    }

    let iframe = document.createElement('iframe');
    iframe.style.backgroundColor = 'transparent';
    iframe.style.borderRadius = '5px';
    iframe.style.width = '100%';
    iframe.style.height = '100%';
    iframe.style.border = '0';
    iframe.style.boxShadow = '0 0 4rem rgba(0,0,0,0.4)';
    modal.appendChild(iframe);

    document.body.prepend(modal);
    document.body.style.overflow = 'hidden';
    iframe.contentWindow.document.open();
    iframe.contentWindow.document.write(page.outerHTML);
    iframe.contentWindow.document.close();

    // Close on click
    modal.addEventListener('click', () => hideErrorFrame(modal));

    // Close on escape key press
    modal.setAttribute('tabindex', 0);
    modal.addEventListener('keydown', e => e.key === 'Escape' && hideErrorFrame(modal));
    modal.focus();
}

const hideErrorFrame = modal => {
    modal.outerHTML = '';
    document.body.style.overflow = 'visible';
}
</script>
