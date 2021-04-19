function extractType(value) {
	var first = value.charAt(0);
	var f;
	switch (first) {
		case "{": // Format C
			f = "json";
			break;
		case "<": // Format D
			f = "xml";
			break;
		default: // Format A or B
			f = "csv";
			break;
	}
	return f;
}

function isLatitude(lat) {
	return isFinite(lat) && Math.abs(lat) <= 90;
}

function isLongitude(lng) {
	return isFinite(lng) && Math.abs(lng) <= 180;
}

function removeDuplicates(arr) {
	let unique_array = []
	for (let i = 0; i < arr.length; i++) {
		if (unique_array.indexOf(arr[i].replace("-", "")) == -1) {
			unique_array.push(arr[i])
		}
	}
	return unique_array
}
function isInt(n) {
	return Number(n) == n && n % 1 === 0;
}
function isFloat(n) {
	return Number(n) == n && n % 1 !== 0;
}

function isTest(deviceSchema) {
	if (deviceSchema.attr.length == 1 && deviceSchema.attr[0].name == "test")
		return true;
	else return false;
}

function flatten(arr) {
    return arr.reduce(function (flat, toFlatten) {
      return flat.concat(Array.isArray(toFlatten) ? flatten(toFlatten) : toFlatten);
    }, []);
}

function getIndexofValueType(array, value_type){
	/* Example of element of array
	gb_value_type
	{	
		id: '659',
		value: 'power',
		label: 'Power',
		type: 'value type',
		parent_id: '',
		parent_value: '',
		children_id: [ '746' ],
		children_value: [ 'W' ]
	}
	*/
	/* gb_value_unit
	 {
		id: '751',
		value: 'l/h',
		label: 'liter per hour',
		type: 'value unit',
		parent_id: [ '716' ],
		parent_value: [ 'water_consumption' ],
		children_id: '',
		children_value: ''
	}
	*/
	for(var i=0; i<array.length; i++){
		if(array[i].value == value_type) return array[i].id
	}
	return -1;
}


module.exports = {
	extractType,
	isLatitude,
	isLongitude,
	removeDuplicates,
	isInt,
	isFloat,
	isTest,
	flatten,
	getIndexofValueType
}