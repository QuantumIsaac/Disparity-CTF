var GLOBAL = {
	CTF_NAME: 'ByteCTF',
	CTF_DATE: 'February 18, 2017 9:00:00'
};

var dat = Date.parse( GLOBAL.CTF_DATE );

var showing_dsp_msg = false;

function compare_dates(){
	var now = Date.now();
	
	var diff = ((dat-now)>0)?(dat-now):0;

	var days  = Math.floor( diff / (1000 * 60 * 60 * 24) );
	var hours = Math.floor(diff / (1000 * 60 * 60)) % 24;
	var minutes = Math.floor(diff / (1000 * 60)) % 60;
	var seconds = Math.floor(diff / 1000) % 60;
	
	var carry_minute = false;
	var carry_hour = false;
	if( seconds == 0 ){
		if( minutes == 0 ){
			if( hours == 0 ){
				if( days == 0 ){
					if( !showing_dsp_msg ){
						db.innerHTML = GLOBAL.CTF_NAME + ' HAS BEGUN.';
						db.style.top = '0';
						db.style.opacity = '1';
						db.style.visibility = 'visible';
						showing_dsp_msg = true;
					}
				}
			}
		}
	}
	
	return {
		days: days,
		hours:  hours,
		minutes: minutes,
		seconds: seconds
	};
}

var de;
var he;
var me;
var se;
var db;

function format(num){
	var str = num.toString();
	if( str.length == 1 ){
		return "0" + str;
	}
	return str;
}

function update_data(){
	d = compare_dates();
	
	de.innerHTML = format(d.days);
	he.innerHTML = format(d.hours);
	me.innerHTML = format(d.minutes);
	se.innerHTML = format(d.seconds);
	if( !showing_dsp_msg ) setTimeout( update_data, 500 );
}

function InitDate(){
	de = document.getElementById('days');
	he = document.getElementById('hours');
	me = document.getElementById('minutes');
	se = document.getElementById('seconds');
	
	db = document.getElementById('disp-msg');
	
	update_data();
}

addEventListener( 'load', InitDate );
