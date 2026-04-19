import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [ "input" ]

    connect() {
        const toggleTrigger = document.createElement("span");
        toggleTrigger.classList.add("js-togglePwdVisibility");
        this.inputTarget.parentNode.insertBefore(toggleTrigger, this.inputTarget);

        toggleTrigger.addEventListener('click', () => {
            if (this.inputTarget.type === 'password') {
                this.inputTarget.type = 'text';
                this.element.classList.add('js-pwdShow');
            } else {
                this.inputTarget.type = 'password';
                this.element.classList.remove('js-pwdShow');
            }
        });
    }
}