(function() {
    var el = window.wp.element.createElement;
    var registerBlockType = window.wp.blocks.registerBlockType;

    // Use a defined logo URL or fallback to an empty string if not defined
    var logoUrl = (typeof getblitzMessagingVars !== 'undefined' && getblitzMessagingVars.logoUrl) ? getblitzMessagingVars.logoUrl : '';

    var blockAttrs = {
        style: {
            padding: '16px',
            backgroundColor: '#ffffff',
            border: '1px solid #e2e8f0',
            borderLeft: '4px solid #7c3aed',
            borderRadius: '4px',
            display: 'flex',
            flexDirection: 'column',
            gap: '8px',
            marginBottom: '1.5em'
        }
    };

    var content = function() {
        return el(
            'div',
            blockAttrs,
            el(
                'div',
                { style: { display: 'flex', alignItems: 'center', gap: '8px' } },
                logoUrl ? el('img', { src: logoUrl, alt: 'GetBlitz Logo', style: { height: '20px', width: 'auto' } }) : null,
                el('strong', { style: { fontSize: '14px', color: '#1e293b' } }, 'Secure SEPA Payments')
            ),
            el('p', { style: { margin: 0, fontSize: '13px', color: '#475569' } }, 'This store uses GetBlitz to process SEPA Instant transfers securely. Transactions are processed directly from your bank.')
        );
    };

    var getblitzIcon = el('svg', { xmlns: 'http://www.w3.org/2000/svg', viewBox: '0 0 24 24', width: 24, height: 24, fill: '#7c3aed' },
        el('path', { d: 'M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5' })
    );

    registerBlockType('getblitz/messaging', {
        title: 'GetBlitz Messaging',
        icon: getblitzIcon,
        category: 'woocommerce',
        description: 'Add a GetBlitz SEPA processing message or trust badge to your checkout page.',
        edit: content,
        save: content
    });
})();
