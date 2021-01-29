(function($){
	function YMapsMarkerField($field) {
		this.$field = $field;
		this.$inputWrapper = $field.find(".acf-ymaps-marker-field").first();
		if (this.$inputWrapper && this.$inputWrapper[0]) {
			this.$input = this.$inputWrapper.find('input[type="hidden"]');
			this.$mapContainer = this.$inputWrapper.find(".ymaps-container");
			this.initialize();
		}
	}

	YMapsMarkerField.prototype.constructor = YMapsMarkerField;

	YMapsMarkerField.prototype.DEFAULT_LAT = 55.751244; // Moscow center lat
	YMapsMarkerField.prototype.DEFAULT_LNG = 37.618423; // Moscow center lng

  YMapsMarkerField.prototype.initialize = function() {
		if (!this.$input || !this.$input[0]) {
			console.error("Could not find input for YMapsMarkerField (key: " +  this.$field.attr("data-key") || "undefined" + ")");
			return; 
		}

		this.initializeMap();
	};
	
  YMapsMarkerField.prototype.__getInitialMapOptions = function() {
		var currentValue = this.getValue();
		var initialLat = currentValue.lat ? parseFloat(currentValue.lat) : this.DEFAULT_LAT;
		var initialLng = currentValue.lng ? parseFloat(currentValue.lng) : this.DEFAULT_LNG;
		var initialZoom = parseInt(this.$inputWrapper.data("zoom"), 10);
		
		return {
			center: [
				(!Number.isNaN(initialLat) && initialLat) ? initialLat : this.DEFAULT_LAT,
				(!Number.isNaN(initialLng) && initialLng) ? initialLng : this.DEFAULT_LNG
			],
			zoom: (!Number.isNaN(initialZoom) && initialZoom) ?  initialZoom : 13,
			controls: [
				"fullscreenControl",
				"zoomControl",
				"searchControl",
				"geolocationControl"
			]
		}
	};

  YMapsMarkerField.prototype.initializeMap = function() {
		if (!this.$mapContainer || !this.$mapContainer[0]) {
			console.error("Could not find ymaps-container for YMapsMarkerField (key: " +  this.$field.attr("data-key") || "undefined" + ")");
			return;
		}

		var self = this;
    window.ymaps.ready(function() {
			var initialMapOptions = self.__getInitialMapOptions();
			self.yandexMap = new window.ymaps.Map(self.$mapContainer[0], initialMapOptions);
			self.update();
			self.__updateMapMarkerButton();
			
			// event handlers
			self.yandexMap.events.add("click", function(e) {
				var coordinates = e.get("coords");
		
				if (coordinates[0] && coordinates[1]) {
					self.mergeValue({
						lat: coordinates[0],
						lng: coordinates[1]
					});
				}
			});
		});
  };

  YMapsMarkerField.prototype.__addMapMarkerButton = function() {
		if(this.__mapMarkerButton) return;
		if (!window.ymaps || !this.yandexMap) {
			console.error("Unable to create map marker button");
			return; 
		}

		this.__mapMarkerButton = new ymaps.control.Button({
			data: {
				content: this.__mapMarker ? "Удалить маркер" : "Добавить маркер", 
			},
			options: {
				float: "right",
			}
		})

		// event handlers
		const self = this;
		this.__mapMarkerButton.events.add("click", function() {
			if(self.__mapMarker) {
				self.mergeValue({ lat: null, lng: null });
			} else {
				const center = self.yandexMap && self.yandexMap.getCenter();
				if (center) {
					self.mergeValue({ lat: center[0], lng: center[1] });
				}
			}
			self.__updateMapMarkerButton();
		})

		this.yandexMap.controls.add(this.__mapMarkerButton);

		return this.__mapMarkerButton;
	}

  YMapsMarkerField.prototype.__updateMapMarkerButton = function() {
		if (!window.ymaps || !this.yandexMap) {
			console.error("Unable to create map marker button");
			return; 
		}
		if(!this.__mapMarkerButton) {
			this.__addMapMarkerButton();
		}

		if (this.__mapMarker) {
			this.__mapMarkerButton.data.set("content", "Удалить маркер");
		} else {
			this.__mapMarkerButton.data.set("content", "Добавить маркер");
		}
	}

  YMapsMarkerField.prototype.__createMapMarker = function(lat, lng) {
		if (!window.ymaps || !this.yandexMap) {
			console.error("Unable to create map marker");
			return; 
		}

		lat = lat ? parseFloat(lat) : this.DEFAULT_LAT;
		lng = lng ? parseFloat(lng) : this.DEFAULT_LNG;

    this.__mapMarker = new window.ymaps.Placemark([
      (!Number.isNaN(lat) && lat) ? lat : this.DEFAULT_LAT,
      (!Number.isNaN(lng) && lng) ? lng : this.DEFAULT_LNG,
		]);
		this.yandexMap.geoObjects.add(this.__mapMarker);
		
		// event handlers
		var self = this;
		this.__mapMarker.events.add("dragend", function(e) {
			var mapMarker = e.get("target");
			var coordinates = mapMarker ? mapMarker.geometry.getCoordinates() : null;
		
			if (coordinates && coordinates[0] && coordinates[1]) {
				self.mergeValue({
					lat: coordinates[0],
					lng: coordinates[1]
				});
			}
		});

		// start
		this.__mapMarker.editor.startEditing();
		this.__updateMapMarkerButton();
		
		return this.__mapMarker;
	}

  YMapsMarkerField.prototype.setMarkerPosition = function(lat, lng) {
		if (!this.__mapMarker) {
			this.__createMapMarker(lat, lng);
			return;
		}
		if (lat, lng) {
			this.__mapMarker.geometry.setCoordinates([lat, lng]);
		}
  };

  YMapsMarkerField.prototype.removeMarker = function() {
		if (this.__mapMarker) {
			this.yandexMap.geoObjects.remove(this.__mapMarker);
			this.__mapMarker = null;
			this.__updateMapMarkerButton();
		}
  };

  YMapsMarkerField.prototype.mergeValue = function(valueToMerge, silent) {
		if (valueToMerge) {
			var currentValue = this.getValue();
			var newValue = Object.assign({}, (currentValue || {}), valueToMerge);
			this.setValue(newValue, silent);
		}
	};

  YMapsMarkerField.prototype.setValue = function(value, silent) {
		var valueAttr = value ? JSON.stringify(value) : "";
		
		acf.val(this.$input, valueAttr);
		
		if(silent) {
			return;
		}
		
		this.renderValue(value);
	};
	
  YMapsMarkerField.prototype.renderValue = function(value) {
		if (value && value.lat && value.lng) {
			this.setMarkerPosition(value.lat, value.lng);
		} else {
			this.removeMarker();
		}
	}

  YMapsMarkerField.prototype.getValue = function() {
		var rawValue = this.$input.val();
		if(rawValue) {
			return JSON.parse(rawValue)
		} else {
			return false;
		}
  };

  YMapsMarkerField.prototype.update = function() {
		this.setValue(this.getValue());
  };
	
	/**
	*  initialize_field
	*
	*  This function will initialize the $field.
	*
	*  @date	30/11/17
	*  @since	5.6.5
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function initialize_field( $field ) {
    var YMapsMarkerFields = [];
    $field.each(function() {
      YMapsMarkerFields.push(new YMapsMarkerField($(this)));
    });
	}
	
	
	if( typeof acf.add_action !== "undefined" ) {
	
		/*
		*  ready & append (ACF5)
		*
		*  These two events are called when a field element is ready for initizliation.
		*  - ready: on page load similar to $(document).ready()
		*  - append: on new DOM elements appended via repeater field or other AJAX calls
		*
		*  @param	n/a
		*  @return	n/a
		*/
		
		acf.add_action("ready_field/type=ymaps_marker_field", initialize_field);
		acf.add_action("append_field/type=ymaps_marker_field", initialize_field);
		
		
	} else {
		
		/*
		*  acf/setup_fields (ACF4)
		*
		*  These single event is called when a field element is ready for initizliation.
		*
		*  @param	event		an event object. This can be ignored
		*  @param	element		An element which contains the new HTML
		*  @return	n/a
		*/
		
		$(document).on("acf/setup_fields", function(e, postbox){
			
			// find all relevant fields
			$(postbox).find('.field[data-field_type="ymaps_marker_field"]').each(function(){
				
				// initialize
				initialize_field( $(this) );
				
			});
		
		});
	
	}

})(jQuery);
