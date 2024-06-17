import { decodeEntities } from "@wordpress/html-entities";
import {useEffect} from "@wordpress/element";

let sheet = document.createElement('style')
sheet.innerHTML = ".wgf_checkout_button:hover {opacity: 0.85;}";
sheet.innerHTML += ".wgf_checkout_button_disabled { opacity: 0.5; cursor: wait; }";
sheet.innerHTML += ".wgf_checkout_button_img { width: 100%; max-width: 320px; }";
document.head.appendChild(sheet);

// const script = document.createElement('script');
// script.src = "https://cdn.wegetfinancing.com/libs/1.0/getfinancing.js";
// document.head.appendChild(script)

let billData = null, shipData = null;

const { registerPaymentMethod } = window.wc.wcBlocksRegistry;
const { getSetting } = window.wc.wcSettings;

const settings = getSetting("wegetfinancing_data", {});

const label = settings.title;
const description = settings.description;

const Content = (props) => {
    const billing = props.billing;
    const shippingData = props.shippingData;
    billData = billing;
    shipData = shippingData;

    const { eventRegistration, emitResponse } = props;
    const { onPaymentProcessing } = eventRegistration;
    useEffect( () => {
        const unsubscribe = onPaymentProcessing( async () => {
            // Here we can do any processing we need, and then emit a response.
            // For example, we might validate a custom field, or perform an AJAX request, and then emit a response indicating it is valid or not.
            let invIdElem = document.getElementById(settings.order_inv_id_field_id),
                wgfHrefElem = document.getElementById("order_wgf_href");
            const inv_id = invIdElem ? invIdElem.value : null;
            const wgf_href = wgfHrefElem ? wgfHrefElem.value : null;
            const customDataIsValid = !!inv_id.length && !!wgf_href.length;

            if ( customDataIsValid ) {
                return {
                    type: emitResponse.responseTypes.SUCCESS,
                    meta: {
                        paymentMethodData: {
                            inv_id,
                            wgf_href,
                        },
                    },
                };
            }

            return {
                type: emitResponse.responseTypes.ERROR,
                message: 'There was an error',
            };
        } );
        // Unsubscribes when this component is unmounted.
        return () => {
            unsubscribe();
        };
    }, [
        emitResponse.responseTypes.ERROR,
        emitResponse.responseTypes.SUCCESS,
        onPaymentProcessing,
    ] );

    return decodeEntities(description);
};

const WgfIcon = () => {
    return <img src={settings.checkout_logo_image_url}
                style={{ float: 'left' }}
                alt={settings.checkout_button_alt}
    />
}

const Label = () => {
    return (<WgfIcon />)
}

registerPaymentMethod({
    name: settings.payment_method_id,
    label: <Label />,
    content: <Content />,
    edit: <Content />,
    canMakePayment: () => true,
    ariaLabel: label,
});

const wgfBtn = document.createElement("a");
wgfBtn.id = "wgf_checkout_button";
wgfBtn.className = "wgf_checkout_button";
wgfBtn.href = "#wgf_checkout";
wgfBtn.style.display = "none";
wgfBtn.style.width = "250px";
wgfBtn.onclick = () => {
    const wgfBtnElement = document.querySelector("#wgf_checkout_button");
    wgfBtnElement.classList.add("wgf_checkout_button_disabled");
    wgfBtnElement.disabled = true;
    wgfFetch();
}

const wgfBtnImage = document.createElement("img");
wgfBtnImage.src = settings.checkout_button_image_url;
wgfBtnImage.alt = settings.checkout_button_alt;
wgfBtnImage.style.maxWidth = "320px";
wgfBtnImage.className = "wgf_checkout_button_img";

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

window.onload = (event) => {
    OnLoadFn();
};

const OnLoadFn = function () {
    const placeOrderBtn = document.querySelector(".wc-block-components-checkout-place-order-button");

    placeOrderBtn.closest("div").append(wgfBtn);
    const wgfBtnElement = document.querySelector("#wgf_checkout_button");
    const radioSelected = document.querySelector('input[name="radio-control-wc-payment-method-options"]:checked');

    radioSelected ? ShowHideButtons(radioSelected, placeOrderBtn, wgfBtnElement) : setTimeout(OnLoadFn, 50);
}

const ShowHideButtons = function (radioSelected, placeOrderBtn, wgfBtnElement) {
    if(radioSelected && "value" in radioSelected && radioSelected.value === settings.payment_method_id) {
        placeOrderBtn.style.display = 'none';
        wgfBtnElement.style.display = 'block';
    }
}

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
            const wgfBtnElement = document.querySelector("#wgf_checkout_button");
            wgfBtnElement.classList.remove("wgf_checkout_button_disabled");
            wgfBtnElement.disabled = false;

            let checkWgfErrorsBlockContent = document.getElementById("wgf-errors-block-content");
            if (checkWgfErrorsBlockContent) {
                checkWgfErrorsBlockContent.innerHTML = "";
            }

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

                    elem ? WgfValidatorMessageOnField(elem, msg) : WgfErrorList(msg);
                }
            }
        }
    }
}

const WgfValidatorMessageOnField = (elem, msg) => {
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

const generateDivError = () => {
    let div = document.createElement("div");
    div.className = 'wc-block-components-validation-error';
    return div;
}

const WgfSuccess = (resp) => {
    const placeOrderBtn = document.querySelector(".wc-block-components-checkout-place-order-button");
    const form = placeOrderBtn.closest("form");

    let invIdElem = document.getElementById(settings.order_inv_id_field_id);
    if (!invIdElem) {
        invIdElem = document.createElement("input");
        invIdElem.id = settings.order_inv_id_field_id;
        invIdElem.name = settings.order_inv_id_field_id;
        invIdElem.type = "hidden";
        form.append(invIdElem)
    }

    invIdElem.value = resp.invId;

    let wgfHrefElem = document.getElementById("order_wgf_href");
    if (!wgfHrefElem) {
        wgfHrefElem = document.createElement("input");
        wgfHrefElem.id = "order_wgf_href";
        wgfHrefElem.name = "order_wgf_href";
        wgfHrefElem.type = "hidden";
        form.append(wgfHrefElem)
    }

    wgfHrefElem.value = resp.href;

    placeOrderBtn.click();

    // if (GetFinancing) {
    //     new GetFinancing(
    //         resp.href,
    //         function () {
    //             const placeOrderBtn = document.querySelector(".wc-block-components-checkout-place-order-button");
    //             placeOrderBtn.click();
    //         }.bind(self),
    //         function () {}
    //     )
    // }
}

const WgfErrorList = (msg) => {
    let checkWgfErrorsBlockContent = document.getElementById("wgf-errors-block-content");
    if (!checkWgfErrorsBlockContent) {
        let wgfErrorsCont = document.createElement("div");
        wgfErrorsCont.className = "woocommerce-notices-wrapper";

        let wgfErrorsBlock = document.createElement("div");
        wgfErrorsBlock.className = "wc-block-components-notice-banner is-error";
        wgfErrorsBlock.role = "alert";
        wgfErrorsBlock.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false">
        <path d="M16.7 7.1l-6.3 8.5-3.3-2.5-.9 1.2 4.5 3.4L17.9 8z"></path>
    </svg>`;
        let wgfErrorsBlockContent = document.createElement("div");
        wgfErrorsBlockContent.className = "wc-block-components-notice-banner__content";
        wgfErrorsBlockContent.id = "wgf-errors-block-content";
        wgfErrorsBlock.append(wgfErrorsBlockContent)
        wgfErrorsCont.append(wgfErrorsBlock);

        const placeOrderBtn = document.querySelector(".wc-block-components-checkout-place-order-button");
        const form = placeOrderBtn.closest("form");
        form.prepend(wgfErrorsCont)
    }

    const wgfErrorsBlockContent = document.getElementById("wgf-errors-block-content");
    wgfErrorsBlockContent.innerHTML += `<li>${msg}</li>`
}