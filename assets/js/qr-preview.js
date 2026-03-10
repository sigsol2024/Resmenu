/**
 * Shared QR code live preview (used by admin template editor and manager template cards).
 * Requires: qrcode.min.js (e.g. from CDN) loaded before this script.
 */
(function() {
    'use strict';

    function adaptQRCodeLibrary() {
        if (typeof QRCode === 'undefined') {
            setTimeout(adaptQRCodeLibrary, 100);
            return;
        }
        var originalQRCode = QRCode;
        window.QRCode = {
            toString: function(text, options, callback) {
                try {
                    var div = document.createElement('div');
                    div.style.position = 'absolute';
                    div.style.left = '-9999px';
                    div.style.top = '-9999px';
                    document.body.appendChild(div);
                    var qr = new originalQRCode(div, {
                        text: text,
                        width: options.width || 300,
                        height: options.width || 300,
                        colorDark: options.color.dark || '#000000',
                        colorLight: options.color.light || '#FFFFFF',
                        correctLevel: originalQRCode.CorrectLevel.M
                    });
                    setTimeout(function() {
                        var canvas = div.querySelector('canvas');
                        if (canvas) {
                            var svg = canvasToSVG(canvas, options.color.dark || '#000000', options.color.light || '#FFFFFF');
                            document.body.removeChild(div);
                            if (callback) callback(null, svg);
                        } else {
                            document.body.removeChild(div);
                            if (callback) callback(new Error('Failed to generate QR code'));
                        }
                    }, 100);
                } catch (error) {
                    console.error('QR generation error:', error);
                    if (callback) callback(error);
                }
            }
        };
        window.QRCodeAdapterReady = true;
    }

    function canvasToSVG(canvas, darkColor, lightColor) {
        var width = canvas.width;
        var height = canvas.height;
        var ctx = canvas.getContext('2d');
        var imageData = ctx.getImageData(0, 0, width, height);
        var data = imageData.data;
        var svg = '<svg width="' + width + '" height="' + height + '" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' + width + ' ' + height + '">';
        svg += '<rect width="' + width + '" height="' + height + '" fill="' + lightColor + '"/>';
        var blockSize = Math.max(1, Math.floor(width / 100));
        for (var y = 0; y < height; y += blockSize) {
            for (var x = 0; x < width; x += blockSize) {
                var idx = (y * width + x) * 4;
                if (idx < data.length && data[idx] < 128) {
                    var w = Math.min(blockSize, width - x);
                    var h = Math.min(blockSize, height - y);
                    svg += '<rect x="' + x + '" y="' + y + '" width="' + w + '" height="' + h + '" fill="' + darkColor + '"/>';
                }
            }
        }
        svg += '</svg>';
        return svg;
    }

    adaptQRCodeLibrary();
})();

function waitForQRCode(callback, maxAttempts) {
    maxAttempts = maxAttempts !== undefined ? maxAttempts : 50;
    if (typeof QRCode !== 'undefined' && typeof QRCode.toString === 'function' && window.QRCodeAdapterReady) {
        callback();
    } else if (maxAttempts > 0) {
        setTimeout(function() { waitForQRCode(callback, maxAttempts - 1); }, 100);
    } else {
        console.error('QRCode library failed to load');
        if (typeof QRCode !== 'undefined' && typeof QRCode.toString === 'function') {
            callback();
        }
    }
}

function applyFrameToSVG(svg, frameConfig, size) {
    var frameType = frameConfig.type;
    var frameColor = frameConfig.color || '#000000';
    var viewBox = '0 0 ' + size + ' ' + size;
    var viewBoxMatch = svg.match(/viewBox="([^"]*)"/);
    if (viewBoxMatch) viewBox = viewBoxMatch[1];
    var frameSvg = '';
    switch (frameType) {
        case 'square':
            frameSvg = '<rect x="0" y="0" width="' + size + '" height="' + size + '" fill="none" stroke="' + frameColor + '" stroke-width="4"/>';
            break;
        case 'rounded':
            frameSvg = '<rect x="0" y="0" width="' + size + '" height="' + size + '" rx="20" ry="20" fill="none" stroke="' + frameColor + '" stroke-width="4"/>';
            break;
        case 'circle':
            var center = size / 2;
            var radius = size / 2 - 2;
            frameSvg = '<circle cx="' + center + '" cy="' + center + '" r="' + radius + '" fill="none" stroke="' + frameColor + '" stroke-width="4"/>';
            break;
        case 'badge':
            frameSvg = '<rect x="0" y="0" width="' + size + '" height="' + size + '" rx="15" ry="15" fill="none" stroke="' + frameColor + '" stroke-width="4"/>';
            var notchWidth = 60;
            var notchHeight = 20;
            var notchX = (size - notchWidth) / 2;
            frameSvg += '<rect x="' + notchX + '" y="-2" width="' + notchWidth + '" height="' + notchHeight + '" rx="10" ry="10" fill="white" stroke="' + frameColor + '" stroke-width="4"/>';
            break;
        default:
            break;
    }
    return svg.replace('</svg>', frameSvg + '</svg>');
}

function addLogoOverlay(container, sizePercent) {
    removeLogoOverlay(container);
    var logoOverlay = document.createElement('div');
    logoOverlay.id = 'qrLogoOverlay';
    logoOverlay.style.cssText = 'position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:' + (sizePercent * 100) + '%;height:' + (sizePercent * 100) + '%;background:white;border-radius:8px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,0.1);border:2px solid #e5e7eb;';
    logoOverlay.innerHTML = '<span style="color:#9ca3af;font-size:12px;">LOGO</span>';
    container.appendChild(logoOverlay);
}

function removeLogoOverlay(container) {
    var existing = container.querySelector('#qrLogoOverlay');
    if (existing) existing.remove();
}

/**
 * Generate QR preview in container.
 * @param {HTMLElement} container - Element to render into (should have position:relative for logo overlay).
 * @param {Object} config - { frame: {}, colors: {}, logo: {} }
 * @param {HTMLElement|null} textContainer - Optional element for frame text below QR.
 * @param {number} [size=300] - QR size in pixels.
 * @param {Function} [onFallback] - Called when library fails to load (e.g. to show snapshot img).
 */
function generateQRPreview(container, config, textContainer, size, onFallback) {
    size = size || 300;
    config = config || {};
    config.frame = config.frame || { type: 'none', text: '', color: '#000000', text_color: '#000000', text_size: 14, bg_enabled: false, bg_color: '#FFFFFF' };
    config.colors = config.colors || { foreground: '#000000', background: '#FFFFFF' };
    config.logo = config.logo || { enabled: false, size: 0.2 };

    if (typeof QRCode === 'undefined' || typeof QRCode.toString !== 'function') {
        container.innerHTML = '<p style="color:var(--muted,#6b7280);">QR Code library loading...</p>';
        waitForQRCode(function() { generateQRPreview(container, config, textContainer, size, onFallback); }, 50);
        return;
    }
    if (!window.QRCodeAdapterReady) {
        container.innerHTML = '<p style="color:var(--muted,#6b7280);">QR Code library loading...</p>';
        var cb = function() { generateQRPreview(container, config, textContainer, size, onFallback); };
        cb.onFallback = onFallback || null;
        waitForQRCode(cb, 50);
        return;
    }

    container.innerHTML = '<p style="color:var(--muted,#6b7280);">Generating preview...</p>';
    var testURL = 'https://example.com/test-qr-preview';
    var margin = 2;

    try {
        QRCode.toString(testURL, {
            type: 'svg',
            width: size,
            margin: margin,
            color: { dark: config.colors.foreground, light: config.colors.background },
            errorCorrectionLevel: 'M'
        }, function(err, svg) {
            if (err) {
                container.innerHTML = '<p style="color:red;">Error generating QR code</p>';
                if (typeof onFallback === 'function') onFallback();
                return;
            }
            if (!svg || typeof svg !== 'string') {
                container.innerHTML = '<p style="color:red;">Invalid QR code generated</p>';
                if (typeof onFallback === 'function') onFallback();
                return;
            }
            var styledSvg = svg;
            if (config.frame.type !== 'none') {
                styledSvg = applyFrameToSVG(svg, config.frame, size);
            }
            container.innerHTML = styledSvg;
            var svgEl = container.querySelector('svg');
            var qrWidth = svgEl ? (parseFloat(svgEl.getAttribute('width')) || parseFloat(svgEl.getBoundingClientRect().width) || size) : size;

            if (textContainer) {
                if (config.frame.text && config.frame.text.trim()) {
                    textContainer.textContent = config.frame.text;
                    textContainer.style.display = 'block';
                    textContainer.style.color = config.frame.text_color || '#000000';
                    textContainer.style.fontSize = (config.frame.text_size || 14) + 'px';
                    textContainer.style.fontWeight = '600';
                    textContainer.style.textAlign = 'center';
                    textContainer.style.width = qrWidth + 'px';
                    textContainer.style.padding = '8px 0';
                    textContainer.style.marginTop = '0';
                    textContainer.style.backgroundColor = config.frame.bg_enabled ? (config.frame.bg_color || '#FFFFFF') : 'transparent';
                    if (config.frame.bg_enabled) textContainer.style.borderRadius = '4px';
                } else {
                    textContainer.style.display = 'none';
                }
            }

            if (config.logo && config.logo.enabled) {
                addLogoOverlay(container, config.logo.size || 0.2);
            } else {
                removeLogoOverlay(container);
            }
        });
    } catch (error) {
        console.error('QR Preview error:', error);
        container.innerHTML = '<p style="color:red;">Error: ' + (error.message || 'Unknown') + '</p>';
        if (typeof onFallback === 'function') onFallback();
    }
}
