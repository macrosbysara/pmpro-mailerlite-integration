import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';
import App from './App';

declare const mbsSettings: { nonce: string; restBase: string };

domReady( () => {
	const el = document.getElementById( 'mbs-settings' );
	if ( ! el ) {
		return;
	}
	const root = createRoot( el );
	root.render(
		<App />
	);
} );
