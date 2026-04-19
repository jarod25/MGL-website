import { startStimulusApp } from '@symfony/stimulus-bridge';
import Carousel from 'stimulus-carousel';
import Lightbox from 'stimulus-lightbox'


// Registers Stimulus controllers from controllers.json and in the controllers/ directory
const app = startStimulusApp(require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
    true,
    /\.(j|t)sx?$/
));
app.register('carousel', Carousel);
app.register('lightbox', Lightbox)

export default app;
// register any custom, 3rd party controllers here