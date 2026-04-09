/**
 * GetBlitz Blocks integration
 */

const settings = window.wc.wcSettings.getSetting('getblitz_data', {});
const labelText = window.wp.htmlEntities.decodeEntities(settings.title) || 'GetBlitz SEPA Instant Transfer';
const description = window.wp.htmlEntities.decodeEntities(settings.description || 'Pay instantly from your bank account via SEPA Instant.');

const LabelComponent = () => {
    return window.wp.element.createElement(
        'span',
        { style: { display: 'flex', alignItems: 'center', gap: '8px', width: '100%' } },
        window.wp.element.createElement('img', {
            src: settings.icon || '',
            alt: labelText,
            style: { height: '20px', width: 'auto', display: settings.icon ? 'block' : 'none' }
        }),
        window.wp.element.createElement('span', null, labelText)
    );
};

const Content = () => {
    return window.wp.element.createElement(
        window.wp.element.RawHTML,
        null,
        description
    );
};

const Block_Gateway = {
    name: 'getblitz',
    label: window.wp.element.createElement(LabelComponent, null),
    content: window.wp.element.createElement(Content, null),
    edit: window.wp.element.createElement(Content, null),
    canMakePayment: () => true,
    ariaLabel: labelText,
    supports: {
        features: ['products', 'refunds']
    }
};

window.wc.wcBlocksRegistry.registerPaymentMethod(Block_Gateway);
