import { decodeEntities } from "@wordpress/html-entities";
// import { useEffect } from '@wordpress/element';

const script = document.createElement('script');
script.src = "https://cdn.wegetfinancing.com/libs/1.0/getfinancing.js";
document.head.appendChild(script)


let billData = null, shipData = null;

const { registerPaymentMethod } = window.wc.wcBlocksRegistry;
const { getSetting } = window.wc.wcSettings;

const settings = getSetting("wegetfinancing_data", {});

const label = settings.title;
const description = settings.description;

// console.log(settings);

const Content = (props) => {
    const billing = props.billing;
    const shippingData = props.shippingData;
    billData = billing;
    shipData = shippingData;

    // const { onCheckoutValidation } = props.eventRegistration;
    // useEffect( () => {
    //     const unsubscribe = onCheckoutValidation( () => false );
    //     console.log(unsubscribe)
    //     return unsubscribe;
    // }, [ onCheckoutValidation ] );

    return decodeEntities(description);
};

const WgfIcon = () => {
    return <img src={settings.checkout_button_image_url}
                style={{ float: 'right', marginRight: '20px' }}
                alt={settings.checkout_button_alt}
    />
}

const Label = () => {
    return (<span style={{ width: '100%' }}>
            {label}
            <WgfIcon />
        </span>
        )
}

registerPaymentMethod({
    name: settings.payment_method_id,
    label: <Label />,
    content: <Content />,
    edit: <Content />,
    canMakePayment: () => true,
    ariaLabel: label,
    placeOrderButtonLabel: "TEST"
});

const wgfBtn = document.createElement("a");
wgfBtn.id = "wgf_checkout_button";
wgfBtn.className = "wgf_checkout_button";
wgfBtn.href = "#wgf_checkout";
wgfBtn.style.display = "none";
wgfBtn.onclick = () => {

    wgfFetch();
}

const wgfBtnImage = document.createElement("img");
wgfBtnImage.src = settings.checkout_button_image_url;
wgfBtnImage.alt = settings.checkout_button_alt;
wgfBtnImage.style.maxWidth = "320px";
wgfBtnImage.className = "wc-block-components-checkout-place-order-button";

wgfBtn.append(wgfBtnImage);

document.addEventListener('input',(e)=> {
    const placeOrderBtn = document.querySelector(".wc-block-components-checkout-place-order-button");

    placeOrderBtn.closest("div").append(wgfBtn);
    const wgfBtnElement = document.querySelector("#wgf_checkout_button");
    if(e.target.getAttribute('name') === "radio-control-wc-payment-method-options") {
        const isWgfActive = e.target.value === settings.payment_method_id;
        placeOrderBtn.style.display = isWgfActive ? 'none' : 'block';
        wgfBtnElement.style.display = isWgfActive ? 'block' : 'none';
    }
})

const matrixField = {
    billing_first_name: "billing-first_name",
    billing_last_name: "billing-last_name",
    billing_country: "billing-country",
    billing_address_1: "billing-address_1",
    billing_address_2: "billing-address_2",
    billing_city: "billing-city",
    billing_state: "billing-state",
    billing_postcode: "billing-postcode",
    billing_phone: "billing-phone",
    billing_email: "email",

    shipping_first_name: "shipping-first_name",
    shipping_last_name: "shipping-last_name",
    shipping_country: "shipping-country",
    shipping_address_1: "shipping-address_1",
    shipping_address_2: "shipping-address_2",
    shipping_city: "shipping-city",
    shipping_state: "shipping-state",
    shipping_postcode: "shipping-postcode",
    shipping_phone: "shipping-phone",
}

const wgfFetch = () => {
    let wgfFunnelData = {};
    wgfFunnelData[settings.billing_first_name] = billData.billingData.first_name;
    wgfFunnelData[settings.billing_last_name] = billData.billingData.last_name;
    wgfFunnelData[settings.billing_country] = billData.billingData.country;
    wgfFunnelData[settings.billing_address_1] = billData.billingData.address_1;
    wgfFunnelData[settings.billing_address_2] = billData.billingData.address_2;
    wgfFunnelData[settings.billing_city] = billData.billingData.city;
    wgfFunnelData[settings.billing_state] = billData.billingData.state;
    wgfFunnelData[settings.billing_postcode] = billData.billingData.postcode;
    wgfFunnelData[settings.billing_phone] = billData.billingData.phone;
    wgfFunnelData[settings.billing_email] = billData.billingData.email;

    wgfFunnelData[settings['ship-to-different-address-checkbox']] = false;

    wgfFunnelData[settings.shipping_first_name] = shipData.shippingAddress.first_name;
    wgfFunnelData[settings.shipping_last_name] = shipData.shippingAddress.last_name;
    wgfFunnelData[settings.shipping_country] = shipData.shippingAddress.country;
    wgfFunnelData[settings.shipping_address_1] = shipData.shippingAddress.address_1;
    wgfFunnelData[settings.shipping_address_2] = shipData.shippingAddress.address_2;
    wgfFunnelData[settings.shipping_city] = shipData.shippingAddress.city;
    wgfFunnelData[settings.shipping_state] = shipData.shippingAddress.state;
    wgfFunnelData[settings.shipping_postcode] = shipData.shippingAddress.postcode;
    wgfFunnelData[settings.shipping_phone] = shipData.shippingAddress.phone;

    let requestNewFunnelData = new FormData();
    requestNewFunnelData.append("action", settings.ajax_action);
    requestNewFunnelData.append("_wpnonce", settings.nonce);
    for (let key in wgfFunnelData) {
        requestNewFunnelData.append(`data[${key}]`, wgfFunnelData[key]);
    }

    fetch(settings.ajax_url, {
        method: "POST",
        body: requestNewFunnelData,
    })
        .then(response => response.json())
        .then(resp => {
            console.log(resp)
            if ("isSuccess" in resp) {
                false === resp.isSuccess ? WgfUnSuccess(resp) : WgfSuccess(resp)
            }
        })
        .catch(err => console.log(err));
}

const WgfUnSuccess = (resp) => {
    if ("violations" in resp) {
        const violations = resp.violations;
        for (let prop in violations) {
            let property = violations[prop];
            if ("fields" in property && "messages" in property) {
                for (let field in property.fields) {
                    let mKey = property.fields[field],
                        msg = property.messages[field],
                        elemId = matrixField[mKey] ?? field,
                        elem = document.getElementById(elemId)
                    ;

                    if (elem) {
                        let div = elem.closest("div"),
                            divErrorMsg = div.querySelector(".wc-block-components-validation-error")
                        if (!divErrorMsg) {
                            let divError = generateDivError();
                            div.append(divError);
                            divErrorMsg = div.querySelector(".wc-block-components-validation-error")
                        }

                        divErrorMsg.innerHTML = `<p>${msg}</p>`;
                        div.classList.add("has-error");
                    }
                }
            }
        }
    }
}

const generateDivError = () => {
    let div = document.createElement("div");
    div.className = 'wc-block-components-validation-error';
    return div;
}

const WgfSuccess = (resp) => {
    if (GetFinancing) {
        new GetFinancing(
            resp.href,
            function () {
                const placeOrderBtn = document.querySelector(".wc-block-components-checkout-place-order-button");
                placeOrderBtn.click();
            }.bind(self),
            function () {}
        )
    }
    /*
 jQuery('#{{ order_inv_id_field_id }}').val(response.invId);
                new GetFinancing(
                    response.href,
                    function() {
                        checkoutButton.click();
                    }.bind(self),
                    function() {}
                );
 */
}

/*
<div class="woocommerce-notices-wrapper">
	<div class="wc-block-components-notice-banner is-error" role="alert">
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false">
			<path d="M16.7 7.1l-6.3 8.5-3.3-2.5-.9 1.2 4.5 3.4L17.9 8z"></path>
		</svg>
		<div class="wc-block-components-notice-banner__content">
			<a href="https://localhost/?page_id=8" tabindex="1" class="button wc-forward wp-element-button">View cart</a> “test product” has been added to your cart.		</div>
	</div>
</div>
 */
