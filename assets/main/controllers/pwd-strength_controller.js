import {Controller} from '@hotwired/stimulus';
import {Popover} from 'bootstrap';

export default class extends Controller {
    static values = {
        strength: Number,
        position: String,
        title: String,
        minLength: String,
        lowercase: String,
        uppercase: String,
        numbers: String,
        specialCharacters: String
    };

    popover;
    passwordInput;
    triggerElement;
    tipElement = null;
    isPopoverVisible = false;
    rules = [
        {key: 'length', selector: '.pwd-strength-rule--length', strength: 0, regex: /.{8,}/, labelValue: 'minLengthValue'},
        {key: 'lowercase', selector: '.pwd-strength-rule--lowercase', strength: 1, regex: /[a-z]+/, labelValue: 'lowercaseValue'},
        {key: 'uppercase', selector: '.pwd-strength-rule--uppercase', strength: 2, regex: /[A-Z]+/, labelValue: 'uppercaseValue'},
        {key: 'numbers', selector: '.pwd-strength-rule--numbers', strength: 3, regex: /[0-9]+/, labelValue: 'numbersValue'},
        {key: 'specialCharacters', selector: '.pwd-strength-rule--special-characters', strength: 4, regex: /[^a-zA-Z0-9]+/, labelValue: 'specialCharactersValue'}
    ];

    connect() {
        this.passwordInput = this.element.querySelector('input');
        this.triggerElement = this.element.querySelector('.tips-popover');
        this.handleFocus = this.handleFocus.bind(this);
        this.handleBlur = this.handleBlur.bind(this);
        this.handleShown = this.handleShown.bind(this);
        this.handleHidden = this.handleHidden.bind(this);

        this.popover = new Popover(
            this.triggerElement,
            {
                placement: this.positionValue,
                fallbackPlacements: ['right', 'left', 'bottom', 'top'],
                boundary: 'viewport',
                container: 'body',
                trigger: 'manual',
                html: true,
                customClass: 'pwd-strength-popover',
                content: this.showTips()
            }
        );

        this.passwordInput?.addEventListener('focus', this.handleFocus);
        this.passwordInput?.addEventListener('blur', this.handleBlur);
        this.triggerElement?.addEventListener('shown.bs.popover', this.handleShown);
        this.triggerElement?.addEventListener('hidden.bs.popover', this.handleHidden);
    }

    disconnect() {
        this.passwordInput?.removeEventListener('focus', this.handleFocus);
        this.passwordInput?.removeEventListener('blur', this.handleBlur);
        this.triggerElement?.removeEventListener('shown.bs.popover', this.handleShown);
        this.triggerElement?.removeEventListener('hidden.bs.popover', this.handleHidden);
        this.popover?.dispose();
    }

    handleInput(event) {
        this.passwordInput = event.target;
        this.showPopover();
        this.updateRules(event.target.value);
    }

    handleFocus(event) {
        this.passwordInput = event.target;
        this.showPopover();
        this.updateRules(event.target.value);
    }

    handleBlur() {
        this.popover?.hide();
    }

    handleShown() {
        this.isPopoverVisible = true;
        this.tipElement = document.getElementById(
            this.triggerElement.getAttribute('aria-describedby')
        );

        this.updateRules(this.passwordInput?.value ?? '');
    }

    handleHidden() {
        this.isPopoverVisible = false;
        this.tipElement = null;
    }

    showTips() {
        const title = this.escapeHtml(this.titleValue);
        const rules = this.activeRules()
            .map((rule) => `<li class="pwd-strength text-danger ${rule.selector.substring(1)}">${this.escapeHtml(this[rule.labelValue])}</li>`)
            .join('');

        return `<h4 class="h5">${title}</h4><ul class="pwd-strength-list mb-0 ps-3">${rules}</ul>`;
    }

    activeRules() {
        return this.rules.filter((rule) => rule.strength <= this.strengthValue);
    }

    showPopover() {
        if (this.isPopoverVisible) {
            return;
        }

        this.isPopoverVisible = true;
        this.popover.show();
    }

    updateRules(password) {
        this.activeRules().forEach((rule) => {
            const ruleElement = this.ruleElement(rule);

            if (!ruleElement) {
                return;
            }

            const isValid = rule.regex.test(password);

            ruleElement.classList.toggle('text-success', isValid);
            ruleElement.classList.toggle('text-danger', !isValid);
        });
    }

    ruleElement(rule) {
        return this.tipElement?.querySelector(rule.selector) ?? null;
    }

    escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
}
