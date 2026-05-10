import { useState, useEffect, useCallback } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

export default function useOptions() {
	const nonce = mbsSettings.nonce;
	const restBase = mbsSettings.restBase;
	if ( ! nonce || ! restBase ) {
		throw new Error(
			'Required settings not found. Make sure the REST API nonce and base URL are correctly localized in the page.'
		);
	}
	const [ settings, setSettings ] = useState< Settings >( {} as Settings );
	const [ groups, setGroups ] = useState< MailerLiteGroup[] >( [] );
	const [ isLoading, setIsLoading ] = useState( true );
	const [ isSaving, setIsSaving ] = useState( false );
	const [ isFetchingGroups, setIsFetchingGroups ] = useState( false );
	const [ notices, setNotices ] = useState<
		{
			content: string;
			politeness?: 'polite' | 'assertive';
			id: string;
			explicitDismiss: boolean;
		}[]
	>( [] );

	// Configure apiFetch nonce once on mount.
	useEffect( () => {
		apiFetch.use( apiFetch.createNonceMiddleware( nonce ) );
	}, [ nonce ] );

	// Load saved settings from the REST API on mount.
	useEffect( () => {
		apiFetch< Settings >( { path: restBase + '/settings' } )
			.then( ( data ) => {
				setSettings( data );
			} )
			.catch( () => {
				setNotices( ( prev ) => [
					...prev,
					{
						content:
							'Failed to load settings. Please refresh the page.',
						politeness: 'assertive' as const,
						id: Math.random().toString( 36 ).slice( 2, 9 ),
						explicitDismiss: false,
					},
				] );
			} )
			.finally( () => {
				setIsLoading( false );
			} );
	}, [ nonce, restBase ] );

	/**
	 * Fetch available MailerLite groups using the saved API key.
	 * The API key must be saved before calling this function.
	 */
	const fetchGroups = useCallback( () => {
		if ( ! settings.apiKey ) {
			setNotices( ( prev ) => [
				...prev,
				{
					content: 'Please save your API key before fetching groups.',
					politeness: 'assertive' as const,
					id: Math.random().toString( 36 ).slice( 2, 9 ),
					explicitDismiss: false,
				},
			] );
			return;
		}
		setIsFetchingGroups( true );
		fetch( 'https://connect.mailerlite.com/api/groups', {
			headers: {
				Authorization: `Bearer ${ settings.apiKey }`,
				'Content-Type': 'application/json',
				Accept: 'application/json',
			},
		} )
			.then( ( response ) => response.json() )
			.then( ( data ) => {
				setGroups( data.data );
				if ( data.data.length === 0 ) {
					setNotices( ( prev ) => [
						...prev,
						{
							content:
								'No groups found. Make sure your API key is saved and your MailerLite account has groups.',
							politeness: 'polite' as const,
							id: Math.random().toString( 36 ).slice( 2, 9 ),
							explicitDismiss: false,
						},
					] );
				}
			} )
			.catch( () => {
				setNotices( ( prev ) => [
					...prev,
					{
						content:
							'Failed to fetch groups. Please save your API key first.',
						politeness: 'assertive' as const,
						id: Math.random().toString( 36 ).slice( 2, 9 ),
						explicitDismiss: false,
					},
				] );
			} )
			.finally( () => {
				setIsFetchingGroups( false );
			} );
	}, [ settings.apiKey ] );

	/** Validate and submit updated settings to the REST API. */
	async function handleSubmit( e: React.FormEvent ) {
		e.preventDefault();
		setIsSaving( true );

		if ( ! settings.apiKey ) {
			setNotices( ( prev ) => [
				...prev,
				{
					content: 'API Key is required.',
					politeness: 'assertive' as const,
					id: Math.random().toString( 36 ).slice( 2, 9 ),
					explicitDismiss: false,
				},
			] );
			setIsSaving( false );
			return;
		}

		try {
			await apiFetch( {
				path: restBase + '/settings',
				method: 'POST',
				data: settings,
			} );
			setNotices( ( prev ) => [
				...prev,
				{
					content: 'Settings saved successfully.',
					explicitDismiss: false,
					id: Math.random().toString( 36 ).slice( 2, 9 ),
				},
			] );
		} catch {
			setNotices( ( prev ) => [
				...prev,
				{
					content: 'Failed to save settings. Please try again.',
					politeness: 'assertive' as const,
					explicitDismiss: false,
					id: Math.random().toString( 36 ).slice( 2, 9 ),
				},
			] );
		} finally {
			setIsSaving( false );
		}
	}

	/** Update a single settings field. */
	const updateField = useCallback(
		( field: keyof Settings, value: string ) => {
			setSettings( ( prev ) => ( {
				...prev,
				[ field ]: value,
			} ) );
			if ( field === 'groupId' ) {
				const selectedGroup = groups.find(
					( group ) => group.id === value
				);
				setSettings( ( prev ) => ( {
					...prev,
					groupName: selectedGroup ? selectedGroup.name : '',
				} ) );
			}
		},
		[ groups ]
	);

	return {
		handleSubmit,
		updateField,
		fetchGroups,
		settings,
		groups,
		notices,
		setNotices,
		isSaving,
		isLoading,
		isFetchingGroups,
	};
}
