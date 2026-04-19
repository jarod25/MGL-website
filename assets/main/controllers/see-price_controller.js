import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    connect() {
        let payable = document.getElementById('event_isPayable_0')
        let notPayable = document.getElementById('event_isPayable_1')
        let priceDiv = document.getElementById('text_price')
        if (payable.checked) {
            priceDiv.classList.remove('d-none');
        }

        if (payable && priceDiv && notPayable) {
            payable.addEventListener('change', () => {
                this.handleChange(payable, priceDiv);
            });
            notPayable.addEventListener('change', () => {
                this.handleChange(payable, priceDiv);
            });
        }
    }

    handleChange(payable, priceDiv) {
        if (payable.checked) {
            priceDiv.classList.remove('d-none');
        } else {
            priceDiv.classList.add('d-none');
        }
    }
}
