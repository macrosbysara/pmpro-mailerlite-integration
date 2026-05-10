declare module '*.css';
declare module '*.scss';

/** Shape of the full settings object returned by the REST API */
type Settings = {
	apiKey: string;
	groupId: string;
};

/** Shape of a single MailerLite group returned by the /groups endpoint */
type MailerLiteGroup = {
	id: string;
	name: string;
};
