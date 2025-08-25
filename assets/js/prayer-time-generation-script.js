// google map api 
// <script async src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCRIIew-eQp2QjI5mRLFOE-qoUnl-qKC38&libraries=places&callback=initMap"></script>-->

  //src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCRIIew-eQp2QjI5mRLFOE-qoUnl-qKC38&libraries=places">

  jQuery(function ($) {
    //=== ON DOCUMENT READY
    const themeDirectory = "<?php echo get_template_directory_uri() ?>";
    const prayerImageDirectory = themeDirectory+'/images/prayer-times/';
    let intervalId;
    const months = utils.monthData();
    const calculationMethods = {
      "MWL" : "Muslim World League",
      "ISNA" : "Islamic Society of North America",
      "Egypt" : "Egyptian General Authority of Survey",
      "Makkah" : "Umm al-Qura University, Makkah",
      "Karachi" : "University of Islamic Sciences, Karachi",
      "Tehran" : "Institute of Geophysics, University of Tehran",
      "Jafari" : "Shia Ithna Ashari (Ja`fari)"
    }
    // const juristicMethods = {
    //   "Hanafi": "Hanafi Method",
    //   "Shafi": "Shafi Method",
    // };

    //=== POPULATE CALCULATION METHOD SELECT DROPDOWN OPTION
    for (let key in calculationMethods) {
      // console.log(key);
      $('.pt-calculate-method-dropdown-js').append(`<option value="${key}">${calculationMethods[key]}</option>`)
    }
    // for (let key in juristicMethods) {
    //   console.log(key);
    //   $('.pt-juristic-method-dropdown-js').append(`<option value="${key}">${juristicMethods[key]}</option>`)
    // }

    //=== POPULATE MONTH DROPDOWN VALUE
    months.forEach((key, index) => {
      const option = `<option value="${key.value}">${key.label}</option>`;
      $('.month-selector-js').append(option);
    })

    /**
     * =====================
     * Initially show the prayer times based on visitors location
     * @type {string}
     */

        
    const getIpDetailsUrl = "https://ipinfo.io/?token=bca72e9d426331";
    fetch(getIpDetailsUrl)
      .then(response => response.json())
      .then(data => {
        const ipLatLon = data.loc;
        // console.log(data.loc);
        const ipLatLonArray = ipLatLon.split(',');
        const ipCoordinates = [ipLatLonArray[0], ipLatLonArray[1]];
        // console.log(ipCoordinates);

        const timezone = data.timezone;
        // console.log(timezone);

        const zoneInfo = moment.tz(timezone);
        // console.log(zoneInfo);

        const offset = zoneInfo.utcOffset() / 60;
        // console.log(offset);


        $('.timezone-js').val(timezone);
        $('.global-lat-js').val(ipLatLonArray[0]);
        $('.global-lon-js').val(ipLatLonArray[1]);
        $('.global-offset-js').val(offset);
        $('.city-name-js').text(data.city);

        decideCalculationMethod(offset);
        //=== TODAY'S PRAYER TIMES
        prayerTimesToday(new Date(), ipCoordinates, offset);
        //=== MONTHLY PRAYER TIMES
        prayerTimesMonthly(parseInt($('.month-selector-js').val()), ipCoordinates, offset);
      })
      .catch(err => console.log(err))



      

    /**
     * =====================
     * Generated todays prayer times
     * @param currentTime
     * @param coordinates
     * @param gmtOffset
     */
    function prayerTimesToday(currentTime, coordinates, gmtOffset) {
      $('.prayer-tiles').removeClass('upcoming');
      $('.prayer-tiles').removeClass('prayer-box');
      $('.prayer-tiles').removeClass('not-prayer-box');
      $('.prayer-tiles').removeClass('upcoming-prayer-box');

      $('.current-date-js').val(currentTime);

      //=== SET CURRENT TIME
      const formattedDateOptions = { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' };
      const formattedDate = currentTime.toLocaleDateString(undefined, formattedDateOptions);
      $('.current-date-js').html(formattedDate);

      prayTimes.setMethod($('.calculation-method-js').val());
      prayTimes.adjust({
        asr: $('.juristic-method-js').val()
      });

      const todaysPrayerTimes = prayTimes.getTimes(currentTime, coordinates, gmtOffset, 'auto', '12h');
      // console.log('today\'s prayer times: ', todaysPrayerTimes);
      const keys = Object.keys(todaysPrayerTimes);
      // console.log("Asr Time:", todaysPrayerTimes['asr']);


      keys.forEach((key, index) => {
        // console.log(key);
        //=== 24H FORMAT DATE
        let timeArray = todaysPrayerTimes[key].split(':');
        let hours = timeArray[0];
        let mins = parseInt(timeArray[1]);
        // console.log(timeArray[1])
        
        let formatIndicator = timeArray[1].split('  ');
        formatIndicator = formatIndicator[1];
        if (formatIndicator === 'pm' && parseInt(hours) !== 12) {
          hours = parseInt(hours) + 12;
        }

        //=== ADDS THE TIME THE ELEMENT
        $('.prayer-' + index + '-js').html(todaysPrayerTimes[key]);

        //=== ADD 24H FORMAT HOURS AND MINUTES AS ATTRIBUTES
        $('.prayer-' + index + '-js').attr('data-hours', hours);
        $('.prayer-' + index + '-js').attr('data-mins', mins);
        $('.prayer-' + index + '-js').attr('data-prayername', key);
        $('.prayer-' + index + '-js').attr('data-prayer-img', prayerImageDirectory+'img-prayer-'+key+'.jpg');
        // Get the current date
        let currentDate = new Date();
        currentDate.setHours(hours);
        currentDate.setMinutes(mins);
        currentDate.setSeconds(0);
        currentDate.setMilliseconds(0);

        // Get the timestamp in milliseconds
        let timestamp = currentDate.getTime();
        let timestampCurrent = currentTime;

        if ((timestampCurrent - timestamp) < 1) {
          $('.prayer-' + index + '-js').closest('.prayer-tiles').addClass('upcoming');
        }

        //=== ADD A CLASS TO THE PRAYER TILES
        $('.prayer-' + index + '-js').closest('.prayer-tiles').addClass('prayer-box');
        if (index === 2) {
          $('.prayer-' + index + '-js').closest('.prayer-tiles').removeClass('prayer-box');
          $('.prayer-' + index + '-js').closest('.prayer-tiles').addClass('not-prayer-box');
        }
      })

      $('.not-prayer-box').removeClass('upcoming');
      $('.prayer-tiles.upcoming').first().addClass('upcoming-prayer-box');
      if (!$('.prayer-tiles.upcoming-prayer-box').length) {
        $('.prayer-tiles').first().addClass('upcoming-prayer-box');
      }

      //=== HIGHLIGHTED UPCOMING PRAYER TIMES
      $('.next-prayer-name-js').html($('.upcoming-prayer-box .prayer-name').text());
      $('.next-prayer-time-js').html($('.upcoming-prayer-box .prayer-time').text());
      let bgImage = $('.upcoming-prayer-box .prayer-time').attr('data-prayer-img');
      $('.upcoming-prayer-tiles .pt-active').css('background-image', 'url('+bgImage+')');
      $('.next-hour-js').val($('.upcoming-prayer-box .prayer-time').attr('data-hours'));
      $('.next-mins-js').val($('.upcoming-prayer-box .prayer-time').attr('data-mins'));

      $('.global-offset-js').val(gmtOffset);

      //=== UPCOMING PRAYER TIMES TICKER
      if(intervalId) clearInterval(intervalId);
      intervalId = setInterval(showPrayerTimeTicker, 1000);
    }

    /**
     * =====================
     * Generate full months prayer times
     * @param month
     * @param coordinates
     * @param gmtOffset
     */
    function prayerTimesMonthly(month, coordinates, gmtOffset) {
      const year = new Date().getFullYear();
      const date = new Date(year, month, 1);
      const endDate = new Date(year, month + 1, 1);
      const generatedTimes = [];

      $('.prayer-table-monthly-js tbody').html('');
      $('.month-name-js').text(getMonthNameByValue($('.month-selector-js').val()));

      createPrintPrayerTimesUrl();

      while (date < endDate) {
        const times = prayTimes.getTimes(date, coordinates, gmtOffset, 'auto', '12h');

        const options = {
          day: "numeric",
          weekday: "short"
        };
        const formattedDate = date.toLocaleDateString("en-US", options).replace(' ', ', ');

        const today = new Date();
        times.isToday =
          date.getMonth() === today.getMonth() &&
          date.getDate() === today.getDate();

        times.salahDate = date.toDateString();
        times.dayName = formattedDate;

        generatedTimes.push(times);

        // next day
        date.setDate(date.getDate() + 1);
      }

      generatedTimes.forEach((key, index) => {
        let prayerTimesRow = `
          <tr>
              <td class="day-name">${key.dayName}</td>
              <td>${key.fajr}</td>
              <td>${key.sunrise}</td>
              <td>${key.dhuhr}</td>
              <td>${key.asr}</td>
              <td>${key.maghrib}</td>
              <td>${key.isha}</td>
          </tr>`;
        $('.prayer-table-monthly-js tbody').append(prayerTimesRow);
      })
    }
    

    /**
     * =====================
     * Get the time by timezone
     * @param timezone
     * @returns {string}
     */
    function getTimeByTimezone(timezone) {
      let options = {
        timeZone: timezone,
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
      };
      return new Date().toLocaleString('en-US', options);
    }

    /**
     * =====================
     * Upcoming prayer times ticker
     */
    function showPrayerTimeTicker(){
      // console.log('OFFSET: ', $('.global-offset-js').val());
      let currentDate = getTimeByTimezone($('.timezone-js').val());

      let timeArray = currentDate.split(':');
      let currentHours = timeArray[0];
      let currentMins = parseInt(timeArray[1]);
      let currentSeconds = parseInt(timeArray[2]);

      let nextHour = $('.next-hour-js').val();
      let nextMin = $('.next-mins-js').val();

      var year = new Date().getFullYear();
      var month = (new Date().getMonth() + 1).toString().padStart(2, '0');
      var day = new Date().getDate().toString().padStart(2, '0');

      // console.log(`CURRENT TIME: ${currentDate}`);
      let consoleString = `current hour: ${currentHours}|\nCurrent Minutes: ${currentMins}|\nCurrent Seconds: ${currentSeconds}\nNext Hour: ${nextHour}|\nNext Minutes: ${nextMin}`;


      var nextPrayerTime = new Date(`${year}-${month}-${day} ${nextHour}:${nextMin}:00`);
      currentDate = new Date(`${year}-${month}-${day} ${currentHours}:${currentMins}:${currentSeconds}`);

      if($('.prayer-tiles-row .prayer-tiles').first().hasClass('upcoming-prayer-box')){
        if(parseInt(currentHours)>12){
          let cH = 24 - parseInt(currentHours);
          let cM = 60 - parseInt(currentMins);
          let cS = 60 - currentSeconds;
          if(cM<60){
            cH = cH - 1;
          }
          let nH = parseInt(nextHour);
          let nM = parseInt(nextMin);

          let rM = cM + nM;
          let rH = cH + nH;
          if(rM>59){
            rM = 0;
            rH = rH + 1;
          }

          nextHour = parseInt(currentHours) - rH;
          nextMin = rM;

          let nextSeconds = 59;

          currentDate = new Date(`${year}-${month}-${day} ${nextHour}:${nextMin}:${currentSeconds}`);
          nextPrayerTime = new Date(`${year}-${month}-${day} ${currentHours}:${currentMins}:00`);
          // console.log('current date: ', currentDate);
          // console.log('pray date: ', nextPrayerTime);
        }
      }

      var timeRemaining = nextPrayerTime - currentDate;

      // Convert the time remaining to hours, minutes, and seconds
      var hours = Math.floor(timeRemaining / (1000 * 60 * 60));
      var minutes = Math.floor((timeRemaining % (1000 * 60 * 60)) / (1000 * 60));
      var seconds = Math.floor((timeRemaining % (1000 * 60)) / 1000);

      // Format the time remaining as a string
      var timeRemainingStr = hours.toString().padStart(2, '0') + ':' +
        minutes.toString().padStart(2, '0') + ':' +
        seconds.toString().padStart(2, '0');

      // Display the ticker
      $('.upcoming-prayer-ticker-js').html(timeRemainingStr);
    }

    /**
     * =====================
     * Decides calculation method based on gmtoffset
     * @param gmt
     */
    function decideCalculationMethod(gmt){
      if(gmt>3.8 && gmt<7.2){
        $('.calculation-method-js').val('Karachi');
        $('.juristic-method-js').val('Hanafi');
      }

      if(gmt>7.5 && gmt<14){
        $('.calculation-method-js').val('MWL');
        $('.juristic-method-js').val('Standard');
      }

      if(gmt>0 && gmt<3.8){
        $('.calculation-method-js').val('MWL');
        $('.juristic-method-js').val('Standard');
      }

      if(gmt<0){
        $('.calculation-method-js').val('ISNA');
        $('.juristic-method-js').val('Standard');
      }

      $('.pt-calculation-method-js').html(calculationMethods[$('.calculation-method-js').val()]);
      $('.pt-calculate-method-dropdown-js').val($('.calculation-method-js').val());

      // $('.pt-juristic-method-js').html(juristicMethods[$('.juristic-method-js').val()]);
      // $('.pt-juristic-method-dropdown-js').val($('.juristic-method-js').val());
    }

    /**
     * =====================
     * Creates print prayer times page url
     */
    function createPrintPrayerTimesUrl(){
      let printPageUrl = $('.btn-print-js').attr('data-url');
      printPageUrl = `${printPageUrl}?month=${$('.month-selector-js').val()}&latitude=${$('.global-lat-js').val()}&longitude=${$('.global-lon-js').val()}&gmtoffset=${$('.global-offset-js').val()}&location=${$('.city-name-js').text()}&calculation_method=${$('.calculation-method-js').val()}&juristic_method=${$('.juristic-method-js').val()}`;
      $('.btn-print-js').attr('href', printPageUrl);
    }

    /**
     * =====================
     * Get the month name by the number representation of the months
     * @param value
     * @returns {string|null}
     */
    function getMonthNameByValue(value) {
      const foundMonth = months.find(month => month.value === parseInt(value));
      return foundMonth ? foundMonth.monthname : null;
    }

    const LOCAL_STORAGE_KEY = 'prayer-times-current-city'

    function getSavedAddress(){
      const cityName =  localStorage.getItem(LOCAL_STORAGE_KEY);
      if(cityName) {
        setPrayerTimes(cityName);
        document.querySelector('.search-location-js').value = cityName;
        document.querySelector('.city-name-js').innerHTML = cityName;
      } 
    }

    function setPrayerTimes(cityName){
      const url = "https://nominatim.openstreetmap.org/search?format=json&limit=3&q=" + cityName;

      fetch(url)
        .then(response => response.json())
        .then(data => {
          const currentLat = data[0].lat;
          const currentLon = data[0].lon;
          $('.global-lat-js').val(currentLat);
          $('.global-lon-js').val(currentLon);

          const zoneUrl = `https://api.timezonedb.com/v2.1/get-time-zone?key=21LBGVALUT5J&format=json&by=position&lat=${data[0].lat}&lng=${data[0].lon}`;
          fetch(zoneUrl)
            .then(response => response.json())
            .then(data => {
              const currentOffset = data.gmtOffset / 60 / 60;

              $('.timezone-js').val(data.zoneName);
              $('.current-date-js').val(new Date(data.formatted));

              decideCalculationMethod(currentOffset);

              //=== TODAY'S PRAYER TIMES
              prayerTimesToday(new Date(data.formatted), [currentLat, currentLon], currentOffset);
              prayerTimesMonthly(parseInt($('.month-selector-js').val()), [currentLat, currentLon], currentOffset)
              $('.loader-div').removeClass('active');
            })
            .catch(err => {
              console.log(err);
              $('.loader-div').removeClass('active');
            })
        })
        .catch(err => {
          console.log(err);
          $('.loader-div').removeClass('active');
        })
    }
    /**
     * =====================
     * Initialize google map place api for address autocomplete
     * Call the 'prayerTimesToday()' and 'prayerTimesMonthly()' on place change event
     */
    function initMap() {
      let addressFieldsSelector = document.getElementById('current-location');
      addressFieldsSelector.style.backgroundImage = "url('https://res.cloudinary.com/secure-api/image/upload/v1683720094/secure-api/Secure-api/images/mjomeg9bme4yuh2tlr5q.png')";
      addressFieldsSelector.style.backgroundRepeat = "no-repeat";
      addressFieldsSelector.style.backgroundPosition = "99% 50%";
      addressFieldsSelector.style.backgroundSize = "auto";

      getSavedAddress();
      // Create the autocomplete object
      const autocomplete = new google.maps.places.Autocomplete(addressFieldsSelector);
      // Set the fields to retrieve from the Places API
      // autocomplete.setFields(['formatted_address']);
      autocomplete.setFields(['address_components', 'formatted_address', 'geometry']);

      // When a place is selected, populate the address fields in your form
      autocomplete.addListener('place_changed', function () {
        $('.loader-div').addClass('active');
        const place = autocomplete.getPlace();
        if (!place.formatted_address) {
          console.log('No address available for this place.');
          return;
        }

        // Do something with the selected address
        // Retrieve the country, state, and city names from the address components
        let streetNumber, routeName, countryName, stateName, cityName, postalCode;
        for (const component of place.address_components) {
          if (component.types.includes('country')) {
            countryName = component.long_name;
          } else if (component.types.includes('administrative_area_level_1')) {
            stateName = component.long_name;
          } else if (component.types.includes('locality') || component.types.includes('postal_town')) {
            cityName = component.long_name;
          } else if (component.types.includes('administrative_area_level_3')) {
            if (!cityName) cityName = component.long_name;
          } else if (component.types.includes('postal_code')) {
            postalCode = component.long_name;
          } else if (component.types.includes('street_number')) {
            streetNumber = component.long_name;
          } else if (component.types.includes('route')) {
            routeName = component.long_name;
          }
        }
        document.querySelector('.search-location-js').value = '';
        if (!cityName) {
          cityName = stateName;
          if (!stateName) {
            cityName = countryName;
          }
        }
        setPrayerTimes(cityName);
        document.querySelector('.search-location-js').value = cityName;
        document.querySelector('.city-name-js').innerHTML = cityName;
      });
    }

    initMap();

    //=== PRAYER TIMES BY MONTH
    $(document).on('change', '.month-selector-js', function () {
      const self = $(this);
      let coordinates = [$('.global-lat-js').val(), $('.global-lon-js').val()];
      prayerTimesMonthly(parseInt(self.val()), coordinates, $('.global-offset-js').val());
    })

    //=== CALCULATION METHOD CHANGE
    $(document).on('change', '.pt-calculate-method-dropdown-js', function (e){
      e.preventDefault();
      $('.loader-div').addClass('active');
      $('.calculation-method-js').val($(this).val());

      let latitude = $('.global-lat-js').val();
      let longitude = $('.global-lon-js').val();
      let timezoneOffset = $('.global-offset-js').val();
      let currentTime = new Date($('.current-date-js').val());

      //=== TODAY'S PRAYER TIMES
      prayerTimesToday(currentTime, [latitude, longitude], timezoneOffset);
      prayerTimesMonthly(parseInt($('.month-selector-js').val()), [latitude, longitude], timezoneOffset)
      $('.loader-div').removeClass('active');
    })

    //=== DOWNLOAD PRAYER TIMES
    $(document).on('click', '.btn-download-pdf-js', function(e) {
      e.preventDefault();
      $('.loader-div').addClass('active');
      let pageHeading = `Prayer Times of ${$('.month-selector-js option:selected').text()} in ${$('.city-name-js').text()}`;

      $('.html-string-js').val($('.html-string-wrapper-js').html());
      $('.page-heading-js').val(pageHeading);
      $('.foot-calculation-method-js').val($('.pt-calculate-method-dropdown-js option:selected').text());
      $('.foot-juristic-method-js').val($('.juristic-method-js').val());
      $('.pdf-generation-form-js').submit();
      setTimeout(function(){
        $('.loader-div').removeClass('active');
      },2000)

    })
  
      
 
  
  });
