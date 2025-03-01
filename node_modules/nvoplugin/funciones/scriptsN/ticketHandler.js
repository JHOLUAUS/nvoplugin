function imprimirPDF(pdfUrl) {
    let iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.src = pdfUrl;
    document.body.appendChild(iframe);
    
    iframe.onload = function () {
        iframe.contentWindow.print();
    };
}
