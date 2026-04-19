import { Controller } from '@hotwired/stimulus';
import { Toast } from 'bootstrap';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    connect() {
        const toast = new Toast(this.element);
        toast.show();
    }
}