function buildFacility() {
	Send.addFacility();
	return false;
}

function addRestriction() {
	Send.addRestriction();
	return false;
}

function removeRestriction(station_id, type_id) {
	Send.removeRestriction(station_id, type_id);
	return false;
}
