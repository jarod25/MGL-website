import {Controller} from '@hotwired/stimulus';
import {Popover} from 'bootstrap';

export default class extends Controller {
    static values = {
        strength: Number,
        position: String
    };

    passwordStrengthValue = 0;
    popover;
    pwd;
    bool = true;

    connect() {
        this.popover = new Popover(this.element.querySelector('.tips-popover'),
            {
                placement: this.positionValue,
                html: true,
                content: '<h4>Vous devez utiliser : </h4>' + this.showTips()
            });
    }

    handleInput(event) {
        this.pwd = event.target.value;
        this.showPopover();
        this.checkStrength(1, /[a-z]+/);
        this.checkStrength(2, /[A-Z]+/);
        this.checkStrength(3, /[0-9]+/);
        this.checkStrength(4, /[^a-zA-Z0-9]+/);
    }

    checkStrength(strength, regex) {
        const span = document.getElementById(strength);
        if (strength <= this.strengthValue) {
            if (this.pwd.match(regex)) {
                this.incrementPasswordStrength();
                span.classList.add(`strength-${strength}`);
            } else {
                span.classList.remove(`strength-${strength}`);
            }
        }
        const lengthSpan = document.getElementById(0);
        if (this.pwd.length >= 8) {
            lengthSpan.classList.add('strength-0');
        } else {
            lengthSpan.classList.remove('strength-0');
        }
        this.popover.update();
    }

    incrementPasswordStrength() {
        this.passwordStrengthValue++;
    }

    showTips() {
        let tipsText = '';
        const tipsTexts = {
            length: '<span id="0" class="pwd-strength">Au moins <strong>8 caractères</strong>.</span>',
            lowercase_letters: '<span id="1" class="pwd-strength">Des <strong>lettres minuscules</strong>.</span>',
            uppercase_letters: '<span id="2" class="pwd-strength">Des <strong>lettres majuscules</strong>.</span>',
            numbers: '<span id="3" class="pwd-strength">Des <strong>chiffres</strong>.</span>',
            special_chars: '<span id="4" class="pwd-strength">Des <strong>caractères spéciaux ($ @ # & ! *)</strong>.</span>'
        };
        for (let i = 0; i <= this.strengthValue; i++) {
            tipsText += `${tipsTexts[Object.keys(tipsTexts)[i]]}<br>`;
        }
        return tipsText;
    }

    showPopover() {
        if (this.bool) {
            this.popover.show();
            this.bool = false;
        }
        document.querySelectorAll('input').forEach((input) => {
            input.addEventListener('click', () => {
                this.popover.hide();
                this.bool = true;
            });
        });
    }
}
