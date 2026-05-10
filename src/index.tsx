import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';
import App from './App';

declare const mbsmlSettings: { nonce: string; restBase: string };

domReady( () => {
	const el = document.getElementById( 'mbsml-settings' );
	if ( ! el ) {
		return;
	}
	const root = createRoot( el );
	root.render(
		<App
			nonce={ mbsmlSettings.nonce }
			restBase={ mbsmlSettings.restBase }
		/>
	);
} );
