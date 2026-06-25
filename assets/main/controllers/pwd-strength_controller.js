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
    rules = [
        {key: 'length', strength: 0, regex: /.{8,}/, labelValue: 'minLengthValue'},
        {key: 'lowercase', strength: 1, regex: /[a-z]+/, labelValue: 'lowercaseValue'},
        {key: 'uppercase', strength: 2, regex: /[A-Z]+/, labelValue: 'uppercaseValue'},
        {key: 'numbers', strength: 3, regex: /[0-9]+/, labelValue: 'numbersValue'},
        {key: 'specialCharacters', strength: 4, regex: /[^a-zA-Z0-9]+/, labelValue: 'specialCharactersValue'}
    ];

    connect() {
        this.passwordInput = this.element.querySelector('input');

        this.popover = new Popover(
            this.element.querySelector('.tips-popover'),
            {
                placement: this.positionValue,
                fallbackPlacements: ['right', 'left', 'bottom', 'top'],
                boundary: 'viewport',
                container: 'body',
                html: true,
                customClass: 'pwd-strength-popover',
                content: this.showTips()
            }
        );
    }

    disconnect() {
        this.popover?.dispose();
    }

    handleInput(event) {
        this.passwordInput = event.target;
        this.showPopover();
        this.updateRules(event.target.value);
        this.popover.update();
    }

    showTips() {
        const title = this.escapeHtml(this.titleValue);
        const rules = this.activeRules()
            .map((rule) => `<span class="pwd-strength" data-pwd-strength-rule="${rule.key}">${this.escapeHtml(this[rule.labelValue])}</span>`)
            .join('');

        return `<h4>${title}</h4>${rules}`;
    }

    activeRules() {
        return this.rules.filter((rule) => rule.strength <= this.strengthValue);
    }

    showPopover() {
        this.popover.show();
    }

    updateRules(password) {
        this.activeRules().forEach((rule) => {
            const ruleElement = this.ruleElement(rule.key);

            if (!ruleElement) {
                return;
            }

            ruleElement.classList.toggle(`strength-${rule.strength}`, rule.regex.test(password));
        });
    }

    ruleElement(rule) {
        return this.popoverTipElement()?.querySelector(`[data-pwd-strength-rule="${rule}"]`);
    }

    popoverTipElement() {
        return this.popover?.getTipElement();
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
