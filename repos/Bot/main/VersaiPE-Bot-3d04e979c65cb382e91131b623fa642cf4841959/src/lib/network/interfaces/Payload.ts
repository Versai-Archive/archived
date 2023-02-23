import OPCodes from './OPCodes';

export default interface Payload {
	t?: string;
	op: OPCodes;
	d: any;
}
