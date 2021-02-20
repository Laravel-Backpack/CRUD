/**
 * JSONViewer - by Roman Makudera 2016 (c) MIT licence.
 */
var JSONViewer = (function(document) {
	var Object_prototype_toString = ({}).toString;
	var DatePrototypeAsString = Object_prototype_toString.call(new Date);
	
	/** @constructor */
	function JSONViewer() {
		this._dom_container = document.createElement("pre");
		this._dom_container.classList.add("json-viewer");
	};

	/**
	 * Visualise JSON object.
	 * 
	 * @param {Object|Array} json Input value
	 * @param {Number} [inputMaxLvl] Process only to max level, where 0..n, -1 unlimited
	 * @param {Number} [inputColAt] Collapse at level, where 0..n, -1 unlimited
	 */
	JSONViewer.prototype.showJSON = function(jsonValue, inputMaxLvl, inputColAt) {
		// Process only to maxLvl, where 0..n, -1 unlimited
		var maxLvl = typeof inputMaxLvl === "number" ? inputMaxLvl : -1; // max level
		// Collapse at level colAt, where 0..n, -1 unlimited
		var colAt = typeof inputColAt === "number" ? inputColAt : -1; // collapse at
		
		this._dom_container.innerHTML = "";
		walkJSONTree(this._dom_container, jsonValue, maxLvl, colAt, 0);
	};

	/**
	 * Get container with pre object - this container is used for visualise JSON data.
	 * 
	 * @return {Element}
	 */
	JSONViewer.prototype.getContainer = function() {
		return this._dom_container;
	};

	/**
	 * Recursive walk for input value.
	 * 
	 * @param {Element} outputParent is the Element that will contain the new DOM
	 * @param {Object|Array} value Input value
	 * @param {Number} maxLvl Process only to max level, where 0..n, -1 unlimited
	 * @param {Number} colAt Collapse at level, where 0..n, -1 unlimited
	 * @param {Number} lvl Current level
	 */
	function walkJSONTree(outputParent, value, maxLvl, colAt, lvl) {
		var isDate = Object_prototype_toString.call(value) === DatePrototypeAsString;
		var realValue = !isDate && typeof value === "object" && value !== null && "toJSON" in value ? value.toJSON() : value;
		if (typeof realValue === "object" && realValue !== null && !isDate) {
			var isMaxLvl = maxLvl >= 0 && lvl >= maxLvl;
			var isCollapse = colAt >= 0 && lvl >= colAt;
			
			var isArray = Array.isArray(realValue);
			var items = isArray ? realValue : Object.keys(realValue);

			if (lvl === 0) {
				// root level
				var rootCount = _createItemsCount(items.length);
				// hide/show
				var rootLink = _createLink(isArray ? "[" : "{");

				if (items.length) {
					rootLink.addEventListener("click", function() {
						if (isMaxLvl) return;

						rootLink.classList.toggle("collapsed");
						rootCount.classList.toggle("hide");

						// main list
						outputParent.querySelector("ul").classList.toggle("hide");
					});

					if (isCollapse) {
						rootLink.classList.add("collapsed");
						rootCount.classList.remove("hide");
					}
				}
				else {
					rootLink.classList.add("empty");
				}

				rootLink.appendChild(rootCount);
				outputParent.appendChild(rootLink); // output the rootLink
			}

			if (items.length && !isMaxLvl) {
				var len = items.length - 1;
				var ulList = document.createElement("ul");
				ulList.setAttribute("data-level", lvl);
				ulList.classList.add("type-" + (isArray ? "array" : "object"));

				items.forEach(function(key, ind) {
					var item = isArray ? key : value[key];
					var li = document.createElement("li");

					if (typeof item === "object") {
						// null && date
						if (!item || item instanceof Date) {
							li.appendChild(document.createTextNode(isArray ? "" : key + ": "));
							li.appendChild(createSimpleViewOf(item ? item : null, true));
						}
						// array & object
						else {
							var itemIsArray = Array.isArray(item);
							var itemLen = itemIsArray ? item.length : Object.keys(item).length;

							// empty
							if (!itemLen) {
								li.appendChild(document.createTextNode(key + ": " + (itemIsArray ? "[]" : "{}")));
							}
							else {
								// 1+ items
								var itemTitle = (typeof key === "string" ? key + ": " : "") + (itemIsArray ? "[" : "{");
								var itemLink = _createLink(itemTitle);
								var itemsCount = _createItemsCount(itemLen);

								// maxLvl - only text, no link
								if (maxLvl >= 0 && lvl + 1 >= maxLvl) {
									li.appendChild(document.createTextNode(itemTitle));
								}
								else {
									itemLink.appendChild(itemsCount);
									li.appendChild(itemLink);
								}

								walkJSONTree(li, item, maxLvl, colAt, lvl + 1);
								li.appendChild(document.createTextNode(itemIsArray ? "]" : "}"));
								
								var list = li.querySelector("ul");
								var itemLinkCb = function() {
									itemLink.classList.toggle("collapsed");
									itemsCount.classList.toggle("hide");
									list.classList.toggle("hide");
								};

								// hide/show
								itemLink.addEventListener("click", itemLinkCb);

								// collapse lower level
								if (colAt >= 0 && lvl + 1 >= colAt) {
									itemLinkCb();
								}
							}
						}
					}
					// simple values
					else {
						// object keys with key:
						if (!isArray) {
							li.appendChild(document.createTextNode(key + ": "));
						}

						// recursive
						walkJSONTree(li, item, maxLvl, colAt, lvl + 1);
					}

					// add comma to the end
					if (ind < len) {
						li.appendChild(document.createTextNode(","));
					}

					ulList.appendChild(li);
				}, this);

				outputParent.appendChild(ulList); // output ulList
			}
			else if (items.length && isMaxLvl) {
				var itemsCount = _createItemsCount(items.length);
				itemsCount.classList.remove("hide");

				outputParent.appendChild(itemsCount); // output itemsCount
			}

			if (lvl === 0) {
				// empty root
				if (!items.length) {
					var itemsCount = _createItemsCount(0);
					itemsCount.classList.remove("hide");

					outputParent.appendChild(itemsCount); // output itemsCount
				}

				// root cover
				outputParent.appendChild(document.createTextNode(isArray ? "]" : "}"));

				// collapse
				if (isCollapse) {
					outputParent.querySelector("ul").classList.add("hide");
				}
			}
		} else {
			// simple values
			outputParent.appendChild( createSimpleViewOf(value, isDate) );
		}
	};

	/**
	 * Create simple value (no object|array).
	 * 
	 * @param  {Number|String|null|undefined|Date} value Input value
	 * @return {Element}
	 */
	function createSimpleViewOf(value, isDate) {
		var spanEl = document.createElement("span");
		var type = typeof value;
		var asText = "" + value;

		if (type === "string") {
			asText = '"' + value + '"';
		} else if (value === null) {
			type = "null";
			//asText = "null";
		} else if (isDate) {
			type = "date";
			asText = value.toLocaleString();
		}

		spanEl.className = "type-" + type;
		spanEl.textContent = asText;

		return spanEl;
	};

	/**
	 * Create items count element.
	 * 
	 * @param  {Number} count Items count
	 * @return {Element}
	 */
	function _createItemsCount(count) {
		var itemsCount = document.createElement("span");
		itemsCount.className = "items-ph hide";
		itemsCount.innerHTML = _getItemsTitle(count);

		return itemsCount;
	};

	/**
	 * Create clickable link.
	 * 
	 * @param  {String} title Link title
	 * @return {Element}
	 */
	function _createLink(title) {
		var linkEl = document.createElement("a");
		linkEl.classList.add("list-link");
		linkEl.href = "javascript:void(0)";
		linkEl.innerHTML = title || "";

		return linkEl;
	};

	/**
	 * Get correct item|s title for count.
	 * 
	 * @param  {Number} count Items count
	 * @return {String}
	 */
	function _getItemsTitle(count) {
		var itemsTxt = count > 1 || count === 0 ? "items" : "item";

		return (count + " " + itemsTxt);
	};

	return JSONViewer;
})(document);
