import {
	Panel,
	PanelBody,
	TextControl,
	SelectControl,
	Button,
	SnackbarList,
	Spinner,
} from '@wordpress/components';
import useOptions from './useOptions';

interface AppProps {
	nonce: string;
	restBase: string;
}

export default function App( { nonce, restBase }: AppProps ) {
	const {
		updateField,
		fetchGroups,
		isSaving,
		isFetchingGroups,
		notices,
		isLoading,
		settings,
		groups,
		handleSubmit,
		setNotices,
	} = useOptions( nonce, restBase );

	if ( isLoading ) {
		return (
			<div
				style={ {
					display: 'flex',
					justifyContent: 'center',
					alignItems: 'center',
					padding: '2rem',
					gap: '1rem',
				} }
			>
				<h2>Loading Settings...</h2>
				<Spinner />
			</div>
		);
	}

	const groupOptions = [
		{ label: '— Select a group —', value: '' },
		...groups.map( ( g ) => ( { label: g.name, value: g.id } ) ),
	];

	return (
		<form
			onSubmit={ handleSubmit }
			style={ {
				display: 'flex',
				flexDirection: 'column',
				gap: '1.5rem',
			} }
		>
			<Panel>
				<PanelBody title="MailerLite API Credentials">
					<p>
						Enter your MailerLite API key. You can find or create
						it under <strong>Integrations → API</strong> in your
						MailerLite account.
					</p>
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label="API Key"
						type="password"
						value={ settings.apiKey }
						onChange={ ( val ) => {
							updateField( 'apiKey', val );
						} }
					/>
				</PanelBody>
				<PanelBody title="Group Selection">
					<p>
						Select the MailerLite group that members will be added
						to after checkout. Save your API key first, then click
						<strong> Fetch Groups</strong> to load the available
						groups.
					</p>
					<div
						style={ {
							display: 'flex',
							alignItems: 'flex-end',
							gap: '1rem',
							marginBottom: '1rem',
						} }
					>
						<Button
							variant="secondary"
							onClick={ fetchGroups }
							disabled={ isFetchingGroups || isSaving }
							isBusy={ isFetchingGroups }
						>
							Fetch Groups
						</Button>
					</div>
					{ groups.length > 0 && (
						<SelectControl
							__next40pxDefaultSize
							__nextHasNoMarginBottom
							label="Group"
							value={ settings.groupId }
							options={ groupOptions }
							onChange={ ( val ) => {
								updateField( 'groupId', val );
							} }
						/>
					) }
					{ groups.length === 0 && settings.groupId && (
						<p style={ { color: '#757575' } }>
							Currently saved Group ID:{ ' ' }
							<code>{ settings.groupId }</code>. Click "Fetch
							Groups" to reload the list.
						</p>
					) }
				</PanelBody>
			</Panel>
			<Button
				style={ { alignSelf: 'flex-end' } }
				variant="primary"
				type="submit"
				disabled={ isSaving || isLoading }
				isBusy={ isSaving }
			>
				Save Settings
			</Button>
			<div style={ { position: 'relative' } }>
				<SnackbarList
					style={ { top: '100%' } }
					notices={ notices }
					onRemove={ ( id ) => {
						setNotices( ( prev ) =>
							prev.filter( ( notice ) => notice.id !== id )
						);
					} }
				/>
			</div>
		</form>
	);
}
