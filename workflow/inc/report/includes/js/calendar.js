/************************************************************************************************************
JS Calendar
Copyright (C) September 2006  DTHMLGoodies.com, Alf Magne Kalleland

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA

Dhtmlgoodies.com., hereby disclaims all copyright interest in this script
written by Alf Magne Kalleland.

Alf Magne Kalleland, 2006
Owner of DHTMLgoodies.com
	
************************************************************************************************************/	

/* Update log:
(C) www.dhtmlgoodies.com, September 2005

Version 1.2, November 8th - 2005 - Added <iframe> background in IE
Version 1.3, November 12th - 2005 - Fixed top bar position in Opera 7
Version 1.4, December 28th - 2005 - Support for Spanish and Portuguese
Version 1.5, January  18th - 2006 - Fixed problem with next-previous buttons after a month has been selected from dropdown
Version 1.6, February 22nd - 2006 - Added variable which holds the path to images.
Format todays date at the bottom by use of the todayStringFormat variable
Pick todays date by clicking on todays date at the bottom of the calendar
Version 2.0	 May, 25th - 2006	  - Added support for time(hour and minutes) and changing year and hour when holding mouse over + and - options. (i.e. instead of click)
Version 2.1	 July, 2nd - 2006	  - Added support for more date formats(example: d.m.yyyy, i.e. one letter day and month).

*/
var languageCode = 'pt-br';

var calendar_display_time = true;

// Format of current day at the bottom of the calendar
// [todayString] = the value of todayString
// [dayString] = day of week (examle: mon, tue, wed...)
// [UCFdayString] = day of week (examle: Mon, Tue, Wed...) ( First letter in uppercase)
// [day] = Day of month, 1..31
// [monthString] = Name of current month
// [year] = Current year
var todayStringFormat = '[todayString] [UCFdayString]. [day]. [monthString] [year]';
var pathToImages = 'images/'; // Relative to your HTML file
var speedOfSelectBoxSliding = 200; // Milliseconds between changing year and hour when holding mouse over "-" and "+" - lower value = faster
var intervalSelectBox_minutes = 5; // Minute select box - interval between each option (5 = default)
var calendar_offsetTop = 0; // Offset - calendar placement - You probably have to modify this value if you're not using a strict doctype
var calendar_offsetLeft = 0; // Offset - calendar placement - You probably have to modify this value if you're not using a strict doctype
var calendarDiv = false;

var MSIE = false;
var Opera = false;
if(navigator.userAgent.indexOf('MSIE')>=0 && navigator.userAgent.indexOf('Opera')<0)MSIE=true;
if(navigator.userAgent.indexOf('Opera')>=0)Opera=true;


switch(languageCode){
    case "pt-br":  /* Brazilian portuguese (pt-br) */
        var monthArray = ['Janeiro','Fevereiro','Mar&ccedil;o','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
        var monthArrayShort = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
        var dayArray = ['Seg','Ter','Qua','Qui','Sex','S&aacute;b','Dom'];
        var weekString = 'Sem.';
        var todayString = 'Hoje &eacute;';
    break;
}

var daysInMonthArray = [31,28,31,30,31,30,31,31,30,31,30,31];
var currentMonth;
var currentYear;
var currentHour;
var currentMinute;
var calendarContentDiv;
var returnDateTo;
var returnFormat;
var activeSelectBoxMonth;
var activeSelectBoxYear;
var activeSelectBoxHour;
var activeSelectBoxMinute;

var iframeObj = false;
//// fix for EI frame problem on time dropdowns 09/30/2006
var iframeObj2 =false;
function EIS_FIX_EI1(where2fixit)
{
    if(!iframeObj2)return;
    iframeObj2.style.display = 'block';
    iframeObj2.style.height =document.getElementById(where2fixit).offsetHeight+1;
    iframeObj2.style.width=document.getElementById(where2fixit).offsetWidth;
    iframeObj2.style.left=getleftPos(document.getElementById(where2fixit))+1-calendar_offsetLeft;
    iframeObj2.style.top=getTopPos(document.getElementById(where2fixit))-document.getElementById(where2fixit).offsetHeight-calendar_offsetTop;
}

function EIS_Hide_Frame()
{
    if(iframeObj2)iframeObj2.style.display = 'none';}
    //// fix for EI frame problem on time dropdowns 09/30/2006
    var returnDateToYear;
    var returnDateToMonth;
    var returnDateToDay;
    var returnDateToHour;
    var returnDateToMinute;

    var inputYear;
    var inputMonth;
    var inputDay;
    var inputHour;
    var inputMinute;
    var calendarDisplayTime = false;

    var selectBoxHighlightColor = '#D60808'; // Highlight color of select boxes
    var selectBoxRolloverBgColor = '#E2EBED'; // Background color on drop down lists(rollover)

    var selectBoxMovementInProgress = false;
    var activeSelectBox = false;

function cancelCalendarEvent()
{
    return false;
}
function isLeapYear(inputYear)
{
    if(inputYear%400==0||(inputYear%4==0&&inputYear%100!=0)) return true;
    return false;	
}
var activeSelectBoxMonth = false;
var activeSelectBoxDirection = false;

function highlightMonthYear()
{
    if(activeSelectBoxMonth)activeSelectBoxMonth.className='';
    activeSelectBox = this;

    if(this.className=='monthYearActive')
    {
        this.className='';	
    }
    else
    {
        this.className = 'monthYearActive';
        activeSelectBoxMonth = this;
    }

    if(this.innerHTML.indexOf('-')>=0 || this.innerHTML.indexOf('+')>=0)
    {
        if(this.className=='monthYearActive')
        selectBoxMovementInProgress = true;
        else
        selectBoxMovementInProgress = false;
        if(this.innerHTML.indexOf('-')>=0)activeSelectBoxDirection = -1; else activeSelectBoxDirection = 1;	
    }
    else selectBoxMovementInProgress = false;
}

function showMonthDropDown()
{
    if(document.getElementById('monthDropDown').style.display=='block')
    {
        document.getElementById('monthDropDown').style.display='none';	
        //// fix for EI frame problem on time dropdowns 09/30/2006
        EIS_Hide_Frame();
    }
    else
    {
        document.getElementById('monthDropDown').style.display='block';		
        document.getElementById('yearDropDown').style.display='none';
        document.getElementById('hourDropDown').style.display='none';
        document.getElementById('minuteDropDown').style.display='none';
        if (MSIE)
        {
            EIS_FIX_EI1('monthDropDown')
        }
        //// fix for EI frame problem on time dropdowns 09/30/2006
    }
}

function showYearDropDown()
{
    if(document.getElementById('yearDropDown').style.display=='block')
    {
        document.getElementById('yearDropDown').style.display='none';
        //// fix for EI frame problem on time dropdowns 09/30/2006
        EIS_Hide_Frame();
    }
    else
    {
        document.getElementById('yearDropDown').style.display='block';
        document.getElementById('monthDropDown').style.display='none';
        document.getElementById('hourDropDown').style.display='none';
        document.getElementById('minuteDropDown').style.display='none';
        if (MSIE)
        {
            EIS_FIX_EI1('yearDropDown')
        }
        //// fix for EI frame problem on time dropdowns 09/30/2006
    }
}

function showHourDropDown()
{
    if(document.getElementById('hourDropDown').style.display=='block')
    {
        document.getElementById('hourDropDown').style.display='none';	
        //// fix for EI frame problem on time dropdowns 09/30/2006
        EIS_Hide_Frame();
    }
    else
    {
        document.getElementById('hourDropDown').style.display='block';	
        document.getElementById('monthDropDown').style.display='none';	
        document.getElementById('yearDropDown').style.display='none';	
        document.getElementById('minuteDropDown').style.display='none';	
        if (MSIE)
        {
            EIS_FIX_EI1('hourDropDown')
        }
        //// fix for EI frame problem on time dropdowns 09/30/2006
    }

}

function showMinuteDropDown()
{
    if(document.getElementById('minuteDropDown').style.display=='block')
    {
        document.getElementById('minuteDropDown').style.display='none';	
        //// fix for EI frame problem on time dropdowns 09/30/2006
        EIS_Hide_Frame();
    }
    else
    {
        document.getElementById('minuteDropDown').style.display='block';	
        document.getElementById('monthDropDown').style.display='none';	
        document.getElementById('yearDropDown').style.display='none';	
        document.getElementById('hourDropDown').style.display='none';	
        if (MSIE)
        {
            EIS_FIX_EI1('minuteDropDown')
        }
        //// fix for EI frame problem on time dropdowns 09/30/2006
    }
}

function selectMonth()
{
    document.getElementById('calendar_month_txt').innerHTML = this.innerHTML
    currentMonth = this.id.replace(/[^\d]/g,'');
    document.getElementById('monthDropDown').style.display='none';
    //// fix for EI frame problem on time dropdowns 09/30/2006
    EIS_Hide_Frame();
    for(var no=0;no<monthArray.length;no++)
    {
        document.getElementById('monthDiv_'+no).style.color='';	
    }
    this.style.color = selectBoxHighlightColor;
    activeSelectBoxMonth = this;
    writeCalendarContent();
}

function selectHour()
{
    document.getElementById('calendar_hour_txt').innerHTML = this.innerHTML
    currentHour = this.innerHTML.replace(/[^\d]/g,'');
    document.getElementById('hourDropDown').style.display='none';
    //// fix for EI frame problem on time dropdowns 09/30/2006
    EIS_Hide_Frame();
    if(activeSelectBoxHour)
    {
        activeSelectBoxHour.style.color='';
    }
    activeSelectBoxHour=this;
    this.style.color = selectBoxHighlightColor;
    }

function selectMinute()
{
    document.getElementById('calendar_minute_txt').innerHTML = this.innerHTML
    currentMinute = this.innerHTML.replace(/[^\d]/g,'');
    document.getElementById('minuteDropDown').style.display='none';
    //// fix for EI frame problem on time dropdowns 09/30/2006
    EIS_Hide_Frame();
    if(activeSelectBoxMinute)
    {
        activeSelectBoxMinute.style.color='';
    }
    activeSelectBoxMinute=this;
    this.style.color = selectBoxHighlightColor;
    }


function selectYear()
{
    document.getElementById('calendar_year_txt').innerHTML = this.innerHTML
    currentYear = this.innerHTML.replace(/[^\d]/g,'');
    document.getElementById('yearDropDown').style.display='none';
    //// fix for EI frame problem on time dropdowns 09/30/2006
    EIS_Hide_Frame();
    if(activeSelectBoxYear)
    {
        activeSelectBoxYear.style.color='';
    }
    activeSelectBoxYear=this;
    this.style.color = selectBoxHighlightColor;
    writeCalendarContent();
}

function switchMonth()
{
    if(this.src.indexOf('left')>=0)
    {
        currentMonth=currentMonth-1;;
        if(currentMonth<0)
        {
            currentMonth=11;
            currentYear=currentYear-1;
        }
    }
    else
    {
        currentMonth=currentMonth+1;;
        if(currentMonth>11)
        {
            currentMonth=0;
            currentYear=currentYear/1+1;
        }
    }
    writeCalendarContent();
}

function createMonthDiv()
{
    var div = document.createElement('DIV');
    div.className='monthYearPicker';
    div.id = 'monthPicker';

    for(var no=0;no<monthArray.length;no++)
    {
        var subDiv = document.createElement('DIV');
        subDiv.innerHTML = monthArray[no];
        subDiv.onmouseover = highlightMonthYear;
        subDiv.onmouseout = highlightMonthYear;
        subDiv.onclick = selectMonth;
        subDiv.id = 'monthDiv_' + no;
        subDiv.style.width = '56px';
        subDiv.onselectstart = cancelCalendarEvent;		
        div.appendChild(subDiv);
        if(currentMonth && currentMonth==no)
        {
            subDiv.style.color = selectBoxHighlightColor;
            activeSelectBoxMonth = subDiv;
        }
    }
    return div;
}

function changeSelectBoxYear(e,inputObj)
{
    if(!inputObj)inputObj =this;
    var yearItems = inputObj.parentNode.getElementsByTagName('DIV');
    if(inputObj.innerHTML.indexOf('-')>=0)
    {
        var startYear = yearItems[1].innerHTML/1 -1;
        if(activeSelectBoxYear)
        {
            activeSelectBoxYear.style.color='';
        }
    }
    else
    {
        var startYear = yearItems[1].innerHTML/1 +1;
        if(activeSelectBoxYear)
        {
            activeSelectBoxYear.style.color='';
        }
    }
    for(var no=1;no<yearItems.length-1;no++)
    {
        yearItems[no].innerHTML = startYear+no-1;	
        yearItems[no].id = 'yearDiv' + (startYear/1+no/1-1);	
    }
    if(activeSelectBoxYear)
    {
        activeSelectBoxYear.style.color='';
        if(document.getElementById('yearDiv'+currentYear))
        {
            activeSelectBoxYear = document.getElementById('yearDiv'+currentYear);
            activeSelectBoxYear.style.color=selectBoxHighlightColor;;
        }
    }
}

function changeSelectBoxHour(e,inputObj)
{
    if(!inputObj) inputObj = this;
    var hourItems = inputObj.parentNode.getElementsByTagName('DIV');
    if(inputObj.innerHTML.indexOf('-')>=0)
    {
        var startHour = hourItems[1].innerHTML/1 -1;
        if(startHour<0)startHour=0;
        if(activeSelectBoxHour)
        {
            activeSelectBoxHour.style.color='';
        }
    }
    else
    {
        var startHour = hourItems[1].innerHTML/1 +1;
        if(startHour>14)startHour = 14;
        if(activeSelectBoxHour)
        {
            activeSelectBoxHour.style.color='';
        }
    }
    var prefix = '';
    for(var no=1;no<hourItems.length-1;no++)
    {
        if((startHour/1 + no/1) < 11)prefix = '0'; else prefix = '';
        hourItems[no].innerHTML = prefix + (startHour+no-1);
        hourItems[no].id = 'hourDiv' + (startHour/1+no/1-1);
    }
    if(activeSelectBoxHour)
    {
        activeSelectBoxHour.style.color='';
        if(document.getElementById('hourDiv'+currentHour))
        {
            activeSelectBoxHour = document.getElementById('hourDiv'+currentHour);
            activeSelectBoxHour.style.color=selectBoxHighlightColor;;
        }
    }
}

function updateYearDiv()
{
    var div = document.getElementById('yearDropDown');
    var yearItems = div.getElementsByTagName('DIV');
    for(var no=1;no<yearItems.length-1;no++)
    {
        yearItems[no].innerHTML = currentYear/1 -6 + no;
        if(currentYear==(currentYear/1 -6 + no))
        {
            yearItems[no].style.color = selectBoxHighlightColor;
            activeSelectBoxYear = yearItems[no];
        }
        else
        {
            yearItems[no].style.color = '';
        }
    }
}

function updateMonthDiv()
{
    for(no=0;no<12;no++)
    {
        document.getElementById('monthDiv_' + no).style.color = '';
    }
    document.getElementById('monthDiv_' + currentMonth).style.color = selectBoxHighlightColor;
    activeSelectBoxMonth = 	document.getElementById('monthDiv_' + currentMonth);
}


function updateHourDiv()
{
    var div = document.getElementById('hourDropDown');
    var hourItems = div.getElementsByTagName('DIV');

    var addHours = 0;
    if((currentHour/1 -6 + 1)<0)
    {
        addHours = (currentHour/1 -6 + 1)*-1;
    }
    for(var no=1;no<hourItems.length-1;no++)
    {
        var prefix='';
        if((currentHour/1 -6 + no + addHours) < 10)prefix='0';
        hourItems[no].innerHTML = prefix +  (currentHour/1 -6 + no + addHours);	
        if(currentHour==(currentHour/1 -6 + no))
        {
            hourItems[no].style.color = selectBoxHighlightColor;
            activeSelectBoxHour = hourItems[no];
        }
        else
        {
        hourItems[no].style.color = '';
        }
    }
}

function updateMinuteDiv()
{
    for(no=0;no<60;no+=intervalSelectBox_minutes)
    {
        var prefix = '';
        if(no<10)prefix = '0';
        document.getElementById('minuteDiv_' + prefix + no).style.color = '';
    }
    if(document.getElementById('minuteDiv_' + currentMinute))
    {
        document.getElementById('minuteDiv_' + currentMinute).style.color = selectBoxHighlightColor;
        activeSelectBoxMinute = document.getElementById('minuteDiv_' + currentMinute);
    }
}

function createYearDiv()
{
    if(!document.getElementById('yearDropDown'))
    {
        var div = document.createElement('DIV');
        div.className='monthYearPicker';
    }
    else
    {
        var div = document.getElementById('yearDropDown');
        var subDivs = div.getElementsByTagName('DIV');
        for(var no=0;no<subDivs.length;no++)
        {
            subDivs[no].parentNode.removeChild(subDivs[no]);	
        }
    }
    var d = new Date();
    if(currentYear)
    {
        d.setFullYear(currentYear);
    }
    var startYear = d.getFullYear()/1 - 5;
    var subDiv = document.createElement('DIV');
    subDiv.innerHTML = '&nbsp;&nbsp;- ';
    subDiv.onclick = changeSelectBoxYear;
    subDiv.onmouseover = highlightMonthYear;
    subDiv.onmouseout = function()
    {
        selectBoxMovementInProgress = false;
    }
    ;
    subDiv.onselectstart = cancelCalendarEvent;
    div.appendChild(subDiv);

    for(var no=startYear;no<(startYear+10);no++)
    {
        var subDiv = document.createElement('DIV');
        subDiv.innerHTML = no;
        subDiv.onmouseover = highlightMonthYear;
        subDiv.onmouseout = highlightMonthYear;
        subDiv.onclick = selectYear;
        subDiv.id = 'yearDiv' + no;
        subDiv.onselectstart = cancelCalendarEvent;	
        div.appendChild(subDiv);
        if(currentYear && currentYear==no)
        {
            subDiv.style.color = selectBoxHighlightColor;
            activeSelectBoxYear = subDiv;
        }
    }
    var subDiv = document.createElement('DIV');
    subDiv.innerHTML = '&nbsp;&nbsp;+ ';
    subDiv.onclick = changeSelectBoxYear;
    subDiv.onmouseover = highlightMonthYear;
    subDiv.onmouseout = function()
    {
        selectBoxMovementInProgress = false;
    }
    ;
    subDiv.onselectstart = cancelCalendarEvent;
    div.appendChild(subDiv);
    return div;
}

/* This function creates the hour div at the bottom bar */

function slideCalendarSelectBox()
{
    if(selectBoxMovementInProgress)
    {
        if(activeSelectBox.parentNode.id=='hourDropDown')
        {
            changeSelectBoxHour(false,activeSelectBox);
        }
        if(activeSelectBox.parentNode.id=='yearDropDown')
        {
            changeSelectBoxYear(false,activeSelectBox);
        }
    }
    setTimeout('slideCalendarSelectBox()',speedOfSelectBoxSliding);
}

function createHourDiv()
{
    if(!document.getElementById('hourDropDown'))
    {
        var div = document.createElement('DIV');
        div.className='monthYearPicker';
    }
    else
    {
        var div = document.getElementById('hourDropDown');
        var subDivs = div.getElementsByTagName('DIV');
        for(var no=0;no<subDivs.length;no++)
        {
            subDivs[no].parentNode.removeChild(subDivs[no]);
        }
    }
    if(!currentHour)currentHour=0;
    var startHour = currentHour/1;
    if(startHour>14)startHour=14;

    var subDiv = document.createElement('DIV');
    subDiv.innerHTML = '&nbsp;&nbsp;- ';
    subDiv.onclick = changeSelectBoxHour;
    subDiv.onmouseover = highlightMonthYear;
    subDiv.onmouseout = function(){ selectBoxMovementInProgress = false;};	
    subDiv.onselectstart = cancelCalendarEvent;
    div.appendChild(subDiv);

    for(var no=startHour;no<startHour+10;no++)
    {
        var prefix = '';
        if(no/1<10)prefix='0';
        var subDiv = document.createElement('DIV');
        subDiv.innerHTML = prefix + no;
        subDiv.onmouseover = highlightMonthYear;
        subDiv.onmouseout = highlightMonthYear;
        subDiv.onclick = selectHour;
        subDiv.id = 'hourDiv' + no;
        subDiv.onselectstart = cancelCalendarEvent;	
        div.appendChild(subDiv);
        if(currentYear && currentYear==no)
        {
            subDiv.style.color = selectBoxHighlightColor;
            activeSelectBoxYear = subDiv;
        }
    }
    var subDiv = document.createElement('DIV');
    subDiv.innerHTML = '&nbsp;&nbsp;+ ';
    subDiv.onclick = changeSelectBoxHour;
    subDiv.onmouseover = highlightMonthYear;
    subDiv.onmouseout = function(){ selectBoxMovementInProgress = false;};
    subDiv.onselectstart = cancelCalendarEvent;
    div.appendChild(subDiv);

    return div;
}
/* This function creates the minute div at the bottom bar */

function createMinuteDiv()
{
    if(!document.getElementById('minuteDropDown'))
    {
        var div = document.createElement('DIV');
        div.className='monthYearPicker';
    }
    else
    {
        var div = document.getElementById('minuteDropDown');
        var subDivs = div.getElementsByTagName('DIV');
        for(var no=0;no<subDivs.length;no++)
        {
            subDivs[no].parentNode.removeChild(subDivs[no]);
        }
    }
    var startMinute = 0;
    var prefix = '';
    for(var no=startMinute;no<60;no+=intervalSelectBox_minutes)
    {
        if(no<10)prefix='0'; else prefix = '';
        var subDiv = document.createElement('DIV');
        subDiv.innerHTML = prefix + no;
        subDiv.onmouseover = highlightMonthYear;
        subDiv.onmouseout = highlightMonthYear;
        subDiv.onclick = selectMinute;
        subDiv.id = 'minuteDiv_' + prefix +  no;
        subDiv.onselectstart = cancelCalendarEvent;
        div.appendChild(subDiv);
        if(currentYear && currentYear==no)
        {
            subDiv.style.color = selectBoxHighlightColor;
            activeSelectBoxYear = subDiv;
        }
    }
    return div;
}

function highlightSelect()
{
    if(this.className=='selectBoxTime')
    {
        this.className = 'selectBoxTimeOver';
        this.getElementsByTagName('IMG')[0].src = pathToImages + 'down_time_over_cal.gif';
    }
    else if(this.className=='selectBoxTimeOver')
    {
        this.className = 'selectBoxTime';
        this.getElementsByTagName('IMG')[0].src = pathToImages + 'down_time_cal.gif';
    }
    if(this.className=='selectBox')
    {
        this.className = 'selectBoxOver';
        this.getElementsByTagName('IMG')[0].src = pathToImages + 'down_over_cal.gif';
    }
    else if(this.className=='selectBoxOver')
    {
        this.className = 'selectBox';
        this.getElementsByTagName('IMG')[0].src = pathToImages + 'down_cal.gif';
    }
}

function highlightArrow()
{
    if(this.src.indexOf('over')>=0)
    {
        if(this.src.indexOf('left')>=0)this.src = pathToImages + 'left_cal.gif';
        if(this.src.indexOf('right')>=0)this.src = pathToImages + 'right_cal.gif';
    }
    else
    {
        if(this.src.indexOf('left')>=0)this.src = pathToImages + 'left_over_cal.gif';
        if(this.src.indexOf('right')>=0)this.src = pathToImages + 'right_over_cal.gif';
    }
}

function highlightClose()
{
    if(this.src.indexOf('over')>=0)
    {
        this.src = pathToImages + 'close_cal.gif';
    }
    else
    {
        this.src = pathToImages + 'close_over_cal.gif';	
    }
}

function closeCalendar()
{
    document.getElementById('yearDropDown').style.display='none';
    document.getElementById('monthDropDown').style.display='none';
    document.getElementById('hourDropDown').style.display='none';
    document.getElementById('minuteDropDown').style.display='none';

    calendarDiv.style.display='none';
    if(iframeObj){
    iframeObj.style.display='none';
    //// //// fix for EI frame problem on time dropdowns 09/30/2006
    EIS_Hide_Frame();}
    if(activeSelectBoxMonth)activeSelectBoxMonth.className='';
    if(activeSelectBoxYear)activeSelectBoxYear.className='';
}

function writeTopBar()
{
    var topBar = document.createElement('DIV');
    topBar.className = 'topBar';
    topBar.id = 'topBar';
    calendarDiv.appendChild(topBar);

    // Left arrow
    var leftDiv = document.createElement('DIV');
    leftDiv.style.marginRight = '1px';
    var img = document.createElement('IMG');
    img.src = pathToImages + 'left_cal.gif';
    img.onmouseover = highlightArrow;
    img.onclick = switchMonth;
    img.onmouseout = highlightArrow;
    leftDiv.appendChild(img);	
    topBar.appendChild(leftDiv);
    if(Opera)leftDiv.style.width = '16px';

    // Right arrow
    var rightDiv = document.createElement('DIV');
    rightDiv.style.marginRight = '1px';
    var img = document.createElement('IMG');
    img.src = pathToImages + 'right_cal.gif';
    img.onclick = switchMonth;
    img.onmouseover = highlightArrow;
    img.onmouseout = highlightArrow;
    rightDiv.appendChild(img);
    if(Opera)rightDiv.style.width = '16px';
    topBar.appendChild(rightDiv);

    // Month selector
    var monthDiv = document.createElement('DIV');
    monthDiv.id = 'monthSelect';
    monthDiv.onmouseover = highlightSelect;
    monthDiv.onmouseout = highlightSelect;
    monthDiv.onclick = showMonthDropDown;
    var span = document.createElement('SPAN');
    span.innerHTML = monthArray[currentMonth];
    span.id = 'calendar_month_txt';
    monthDiv.appendChild(span);
    var img = document.createElement('IMG');
    img.src = pathToImages + 'down_cal.gif';
    img.style.position = 'absolute';
    img.style.right = '0px';
    monthDiv.appendChild(img);
    monthDiv.className = 'selectBox';
    if(Opera)
    {
        img.style.cssText = 'float:right;position:relative';
        img.style.position = 'relative';
        img.style.styleFloat = 'right';
    }
    topBar.appendChild(monthDiv);
    var monthPicker = createMonthDiv();
    monthPicker.style.left = '37px';
    monthPicker.style.top = monthDiv.offsetTop + monthDiv.offsetHeight + 1 + 'px';
    monthPicker.style.width ='60px';
    monthPicker.id = 'monthDropDown';

    calendarDiv.appendChild(monthPicker);

    // Year selector
    var yearDiv = document.createElement('DIV');
    yearDiv.onmouseover = highlightSelect;
    yearDiv.onmouseout = highlightSelect;
    yearDiv.onclick = showYearDropDown;
    var span = document.createElement('SPAN');
    span.innerHTML = currentYear;
    span.id = 'calendar_year_txt';
    yearDiv.appendChild(span);
    topBar.appendChild(yearDiv);
    var img = document.createElement('IMG');
    img.src = pathToImages + 'down_cal.gif';
    yearDiv.appendChild(img);
    yearDiv.className = 'selectBox';
    if(Opera)
    {
        yearDiv.style.width = '50px';
        img.style.cssText = 'float:right';
        img.style.position = 'relative';
        img.style.styleFloat = 'right';
    }
    var yearPicker = createYearDiv();
    yearPicker.style.left = '113px';
    yearPicker.style.top = monthDiv.offsetTop + monthDiv.offsetHeight + 1 + 'px';
    yearPicker.style.width = '35px';
    yearPicker.id = 'yearDropDown';
    calendarDiv.appendChild(yearPicker);

    var img = document.createElement('IMG');
    img.src = pathToImages + 'close_cal.gif';
    img.style.styleFloat = 'right';
    img.onmouseover = highlightClose;
    img.onmouseout = highlightClose;
    img.onclick = closeCalendar;
    topBar.appendChild(img);

    if(!document.all)
    {
        img.style.position = 'absolute';
        img.style.right = '2px';
    }
}

function writeCalendarContent()
{
    var calendarContentDivExists = true;
    if(!calendarContentDiv)
    {
        calendarContentDiv = document.createElement('DIV');
        calendarDiv.appendChild(calendarContentDiv);
        calendarContentDivExists = false;
    }
    currentMonth = currentMonth/1;
    var d = new Date();

    d.setFullYear(currentYear);
    d.setDate(1);
    d.setMonth(currentMonth);

    var dayStartOfMonth = d.getDay();
    if(dayStartOfMonth==0)dayStartOfMonth=7;
    dayStartOfMonth--;

    document.getElementById('calendar_year_txt').innerHTML = currentYear;
    document.getElementById('calendar_month_txt').innerHTML = monthArray[currentMonth];
    document.getElementById('calendar_hour_txt').innerHTML = currentHour;
    document.getElementById('calendar_minute_txt').innerHTML = currentMinute;

    var existingTable = calendarContentDiv.getElementsByTagName('TABLE');
    if(existingTable.length>0)
    {
        calendarContentDiv.removeChild(existingTable[0]);
    }

    var calTable = document.createElement('TABLE');
    calTable.width = '100%';
    calTable.cellSpacing = '0';
    calendarContentDiv.appendChild(calTable);

    var calTBody = document.createElement('TBODY');
    calTable.appendChild(calTBody);
    var row = calTBody.insertRow(-1);
    row.className = 'calendar_week_row';
    var cell = row.insertCell(-1);
    cell.innerHTML = weekString;
    cell.className = 'calendar_week_column';
    cell.style.backgroundColor = selectBoxRolloverBgColor;

    for(var no=0;no<dayArray.length;no++)
    {
        var cell = row.insertCell(-1);
        cell.innerHTML = dayArray[no]; 
    }

    var row = calTBody.insertRow(-1);
    var cell = row.insertCell(-1);
    cell.className = 'calendar_week_column';
    cell.style.backgroundColor = selectBoxRolloverBgColor;
    var week = getWeek(currentYear,currentMonth,1);
    cell.innerHTML = week;		// Week
    for(var no=0;no<dayStartOfMonth;no++)
    {
        var cell = row.insertCell(-1);
        cell.innerHTML = '&nbsp;';
    }

    var colCounter = dayStartOfMonth;
    var daysInMonth = daysInMonthArray[currentMonth];
    if(daysInMonth==28)
    {
        if(isLeapYear(currentYear)) daysInMonth=29;
    }

    for(var no=1;no<=daysInMonth;no++)
    {
        d.setDate(no-1);
        if(colCounter>0 && colCounter%7==0)
        {
            var row = calTBody.insertRow(-1);
            var cell = row.insertCell(-1);
            cell.className = 'calendar_week_column';
            var week = getWeek(currentYear,currentMonth,no);
            cell.innerHTML = week; // Week
            cell.style.backgroundColor = selectBoxRolloverBgColor;
        }
        var cell = row.insertCell(-1);
        if(currentYear==inputYear && currentMonth == inputMonth && no==inputDay)
        {
            cell.className='activeDay';
        }
        cell.innerHTML = no;
        cell.onclick = pickDate;
        colCounter++;
    }

    if(!document.all){
        if(calendarContentDiv.offsetHeight)
        document.getElementById('topBar').style.top = calendarContentDiv.offsetHeight + document.getElementById('timeBar').offsetHeight + document.getElementById('topBar').offsetHeight -1 + 'px';
        else
        {
           document.getElementById('topBar').style.top = '';
          document.getElementById('topBar').style.bottom = '0px';
        }
    }

    if(iframeObj)
    {
        if(!calendarContentDivExists)setTimeout('resizeIframe()',350);else setTimeout('resizeIframe()',10);
    }
}

function resizeIframe()
{
    iframeObj.style.width = calendarDiv.offsetWidth + 'px';
    iframeObj.style.height = calendarDiv.offsetHeight + 'px' ;
}

function pickTodaysDate()
{
    var d = new Date();
    currentMonth = d.getMonth();
    currentYear = d.getFullYear();
    pickDate(false,d.getDate());
}

function pickDate(e,inputDay)
{
    var month = currentMonth/1 +1;
    if(month<10)month = '0' + month;
    var day;
    if(!inputDay && this)day = this.innerHTML; else day = inputDay;

    if(day/1<10)day = '0' + day;
    if(returnFormat)
    {
        returnFormat = returnFormat.replace('dd',day);
        returnFormat = returnFormat.replace('mm',month);
        returnFormat = returnFormat.replace('yyyy',currentYear);
        returnFormat = returnFormat.replace('hh',currentHour);
        returnFormat = returnFormat.replace('ii',currentMinute);
        returnFormat = returnFormat.replace('d',day/1);
        returnFormat = returnFormat.replace('m',month/1);
        returnDateTo.value = returnFormat;
        try
        {
            returnDateTo.onchange();
        }
        catch(e)
        {
        }
    }
    else
    {
        for(var no=0;no<returnDateToYear.options.length;no++)
        {
            if(returnDateToYear.options[no].value==currentYear)
            {
                returnDateToYear.selectedIndex=no;
                break;
            }
        }
        for(var no=0;no<returnDateToMonth.options.length;no++)
        {
            if(returnDateToMonth.options[no].value==parseInt(month))
            {
                returnDateToMonth.selectedIndex=no;
                break;
            }
        }
        for(var no=0;no<returnDateToDay.options.length;no++)
        {
            if(returnDateToDay.options[no].value==parseInt(day))
            {
                returnDateToDay.selectedIndex=no;
                break;
            }
        }
        if(calendarDisplayTime)
        {
            for(var no=0;no<returnDateToHour.options.length;no++)
            {
                if(returnDateToHour.options[no].value==parseInt(currentHour))
                {
                    returnDateToHour.selectedIndex=no;
                    break;
                }
            }
            for(var no=0;no<returnDateToMinute.options.length;no++)
            {
                if(returnDateToMinute.options[no].value==parseInt(currentMinute))
                {
                    returnDateToMinute.selectedIndex=no;
                    break;
                }
            }
        }
    }
    closeCalendar();
}

// This function is from http://www.codeproject.com/csharp/gregorianwknum.asp
// Only changed the month add
function getWeek(year,month,day)
{
    day = day/1;
    year = year/1;
    month = month/1 + 1; //use 1-12
    var a = Math.floor((14-(month))/12);
    var y = year+4800-a;
    var m = (month)+(12*a)-3;
    var jd = day + Math.floor(((153*m)+2)/5) + (365*y) + Math.floor(y/4) - Math.floor(y/100) + Math.floor(y/400) - 32045;      // (gregorian calendar)
    var d4 = (jd+31741-(jd%7))%146097%36524%1461;
    var L = Math.floor(d4/1460);
    var d1 = ((d4-L)%365)+L;
    NumberOfWeek = Math.floor(d1/7) + 1;
    return NumberOfWeek;
}

function writeTimeBar()
{
    var timeBar = document.createElement('DIV');
    timeBar.id = 'timeBar';
    timeBar.className = 'timeBar';	
    var subDiv = document.createElement('DIV');
    subDiv.innerHTML = 'Time:';
    //timeBar.appendChild(subDiv);
    // Year selector
    var hourDiv = document.createElement('DIV');
    hourDiv.onmouseover = highlightSelect;
    hourDiv.onmouseout = highlightSelect;
    hourDiv.onclick = showHourDropDown;
    hourDiv.style.width = '30px';
    var span = document.createElement('SPAN');		
    span.innerHTML = currentHour;
    span.id = 'calendar_hour_txt';
    hourDiv.appendChild(span);
    timeBar.appendChild(hourDiv);
    var img = document.createElement('IMG');
    img.src = pathToImages + 'down_time_cal.gif';
    hourDiv.appendChild(img);
    hourDiv.className = 'selectBoxTime';
    if(Opera)
    {
        hourDiv.style.width = '30px';
        img.style.cssText = 'float:right';
        img.style.position = 'relative';
        img.style.styleFloat = 'right';
    }
    var hourPicker = createHourDiv();
    hourPicker.style.left = '130px';
    //hourPicker.style.top = monthDiv.offsetTop + monthDiv.offsetHeight + 1 + 'px';
    hourPicker.style.width = '35px';
    hourPicker.id = 'hourDropDown';
    calendarDiv.appendChild(hourPicker);

    // Add Minute picker
    // Year selector
    var minuteDiv = document.createElement('DIV');
    minuteDiv.onmouseover = highlightSelect;
    minuteDiv.onmouseout = highlightSelect;
    minuteDiv.onclick = showMinuteDropDown;
    minuteDiv.style.width = '30px';
    var span = document.createElement('SPAN');
    span.innerHTML = currentMinute;
    span.id = 'calendar_minute_txt';
    minuteDiv.appendChild(span);
    timeBar.appendChild(minuteDiv);
    var img = document.createElement('IMG');
    img.src = pathToImages + 'down_time_cal.gif';
    minuteDiv.appendChild(img);
    minuteDiv.className = 'selectBoxTime';
    if(Opera)
    {
        minuteDiv.style.width = '30px';
        img.style.cssText = 'float:right';
        img.style.position = 'relative';
        img.style.styleFloat = 'right';
    }
    var minutePicker = createMinuteDiv();
    minutePicker.style.left = '167px';
    //minutePicker.style.top = monthDiv.offsetTop + monthDiv.offsetHeight + 1 + 'px';
    minutePicker.style.width = '35px';
    minutePicker.id = 'minuteDropDown';
    calendarDiv.appendChild(minutePicker);

    return timeBar;
    }

function writeBottomBar()
{
    var d = new Date();
    var bottomBar = document.createElement('DIV');
    bottomBar.id = 'bottomBar';
    bottomBar.style.cursor = 'pointer';
    bottomBar.className = 'todaysDate';
    // var todayStringFormat = '[todayString] [dayString] [day] [monthString] [year]';	;;

    var subDiv = document.createElement('DIV');
    subDiv.onclick = pickTodaysDate;
    subDiv.id = 'todaysDateString';
    subDiv.style.width = (calendarDiv.offsetWidth - 95) + 'px';
    var day = d.getDay();
    if(day==0)day = 7;
    day--;

    var bottomString = todayStringFormat;
    bottomString = bottomString.replace('[monthString]',monthArrayShort[d.getMonth()]);
    bottomString = bottomString.replace('[day]',d.getDate());
    bottomString = bottomString.replace('[year]',d.getFullYear());
    bottomString = bottomString.replace('[dayString]',dayArray[day].toLowerCase());
    bottomString = bottomString.replace('[UCFdayString]',dayArray[day]);
    bottomString = bottomString.replace('[todayString]',todayString);

    subDiv.innerHTML = todayString + ': ' + d.getDate() + '. ' + monthArrayShort[d.getMonth()] + ', ' +  d.getFullYear() ;
    subDiv.innerHTML = bottomString ;
    bottomBar.appendChild(subDiv);

    var timeDiv = writeTimeBar();
    bottomBar.appendChild(timeDiv);
    calendarDiv.appendChild(bottomBar);	
}

function getTopPos(inputObj)
{
    var returnValue = inputObj.offsetTop + inputObj.offsetHeight;
    while((inputObj = inputObj.offsetParent) != null)returnValue += inputObj.offsetTop;
    return returnValue + calendar_offsetTop;
}

function getleftPos(inputObj)
{
    var returnValue = inputObj.offsetLeft;
    while((inputObj = inputObj.offsetParent) != null)returnValue += inputObj.offsetLeft;
    return returnValue + calendar_offsetLeft;
}

function positionCalendar(inputObj)
{
    calendarDiv.style.left = getleftPos(inputObj) + 'px';
    calendarDiv.style.top = getTopPos(inputObj) + 'px';
    if(iframeObj)
    {
        iframeObj.style.left = calendarDiv.style.left;
        iframeObj.style.top =  calendarDiv.style.top;
        //// fix for EI frame problem on time dropdowns 09/30/2006
        iframeObj2.style.left = calendarDiv.style.left;
        iframeObj2.style.top =  calendarDiv.style.top;
    }
}

function initCalendar()
{
    if(MSIE)
    {
        iframeObj = document.createElement('IFRAME');
        iframeObj.style.filter = 'alpha(opacity=0)';
        iframeObj.style.position = 'absolute';
        iframeObj.border='0px';
        iframeObj.style.border = '0px';
        iframeObj.style.backgroundColor = '#FF0000';
        //// fix for EI frame problem on time dropdowns 09/30/2006
        iframeObj2 = document.createElement('IFRAME');
        iframeObj2.style.position = 'absolute';
        iframeObj2.border='0px';
        iframeObj2.style.border = '0px';
        iframeObj2.style.height = '1px';
        iframeObj2.style.width = '1px';
        document.body.appendChild(iframeObj2);
        //// fix for EI frame problem on time dropdowns 09/30/2006
        // Added fixed for HTTPS
        iframeObj2.src = 'blank.html';
        iframeObj.src = 'blank.html';
        document.body.appendChild(iframeObj);
    }

    calendarDiv = document.createElement('DIV');
    calendarDiv.id = 'calendarDiv';
    calendarDiv.style.zIndex = 1000;
    slideCalendarSelectBox();

    document.body.appendChild(calendarDiv);
    writeBottomBar();	
    writeTopBar();
    if(!currentYear)
    {
        var d = new Date();
        currentMonth = d.getMonth();
        currentYear = d.getFullYear();
    }
    writeCalendarContent();
}

function setTimeProperties()
{
    if(!calendarDisplayTime)
    {
        document.getElementById('timeBar').style.display='none'; 
        document.getElementById('timeBar').style.visibility='hidden'; 
        document.getElementById('todaysDateString').style.width = '100%';
    }
    else
    {
        document.getElementById('timeBar').style.display='block';
        document.getElementById('timeBar').style.visibility='visible';
        document.getElementById('hourDropDown').style.top = document.getElementById('calendar_minute_txt').parentNode.offsetHeight + calendarContentDiv.offsetHeight + document.getElementById('topBar').offsetHeight + 'px';
        document.getElementById('minuteDropDown').style.top = document.getElementById('calendar_minute_txt').parentNode.offsetHeight + calendarContentDiv.offsetHeight + document.getElementById('topBar').offsetHeight + 'px';
        document.getElementById('minuteDropDown').style.right = '50px';
        document.getElementById('hourDropDown').style.right = '50px';
        document.getElementById('todaysDateString').style.width = '115px';
    }
}

function calendarSortItems(a,b)
{
    return a/1 - b/1;
}


function displayCalendar(inputField,format,buttonObj,displayTime,timeInput)
{
    if(displayTime) calendarDisplayTime=true; else calendarDisplayTime = false;
    if(inputField.value.length==10)
    {
        if(!format.match(/^[0-9]*?$/gi))
        {
            var items = inputField.value.split(/[^0-9]/gi);
            var positionArray = new Array();
            positionArray['m'] = format.indexOf('mm');
            if(positionArray['m']==-1)positionArray['m'] = format.indexOf('m');
            positionArray['d'] = format.indexOf('dd');
            if(positionArray['d']==-1)positionArray['d'] = format.indexOf('d');
            positionArray['y'] = format.indexOf('yyyy');
            positionArray['h'] = format.indexOf('hh');
            positionArray['i'] = format.indexOf('ii');
            var positionArrayNumeric = Array();
            positionArrayNumeric[0] = positionArray['m'];
            positionArrayNumeric[1] = positionArray['d'];
            positionArrayNumeric[2] = positionArray['y'];
            positionArrayNumeric[3] = positionArray['h'];
            positionArrayNumeric[4] = positionArray['i'];
            positionArrayNumeric = positionArrayNumeric.sort(calendarSortItems);
            var itemIndex = -1;
            currentHour = '00';
            currentMinute = '00';
            for(var no=0;no<positionArrayNumeric.length;no++)
            {
                if(positionArrayNumeric[no]==-1)continue;
                itemIndex++;
                if(positionArrayNumeric[no]==positionArray['m'])
                {
                    currentMonth = items[itemIndex]-1;
                    continue;
                }
                if(positionArrayNumeric[no]==positionArray['y'])
                {
                    currentYear = items[itemIndex];
                    continue;
                }
                if(positionArrayNumeric[no]==positionArray['d'])
                {
                    tmpDay = items[itemIndex];
                    continue;
                }
                if(positionArrayNumeric[no]==positionArray['h'])
                {
                    currentHour = items[itemIndex];
                    continue;
                }
                if(positionArrayNumeric[no]==positionArray['i'])
                {
                    currentMinute = items[itemIndex];
                    continue;
                }
            }

            currentMonth = currentMonth / 1;
            tmpDay = tmpDay / 1;
        }
        else
        {
            var monthPos = format.indexOf('mm');
            currentMonth = inputField.value.substr(monthPos,2)/1 -1;	
            var yearPos = format.indexOf('yyyy');
            currentYear = inputField.value.substr(yearPos,4);		
            var dayPos = format.indexOf('dd');
            tmpDay = inputField.value.substr(dayPos,2);		
            var hourPos = format.indexOf('hh');
            if(hourPos>=0)
            {
                tmpHour = inputField.value.substr(hourPos,2);	
                currentHour = tmpHour;
            }
            else
            {
            currentHour = '00';
            }
            var minutePos = format.indexOf('ii');
            if(minutePos>=0)
            {
                tmpMinute = inputField.value.substr(minutePos,2);	
                currentMinute = tmpMinute;
            }
            else
            {
                currentMinute = '00';
            }
        }
    }
    else
    {
        var d = new Date();
        currentMonth = d.getMonth();
        currentYear = d.getFullYear();
        currentHour = '08';
        currentMinute = '00';
        tmpDay = d.getDate();
    }
    inputYear = currentYear;
    inputMonth = currentMonth;
    inputDay = tmpDay/1;
    if(!calendarDiv)
    {
        initCalendar();
    }
    else
    {
        if(calendarDiv.style.display=='block')
        {
            closeCalendar();
            return false;
        }
    writeCalendarContent();
    }
    returnFormat = format;
    returnDateTo = inputField;
    positionCalendar(buttonObj);
    calendarDiv.style.visibility = 'visible';	
    calendarDiv.style.display = 'block';	
    if(iframeObj)
    {
        iframeObj.style.display = '';
        iframeObj.style.height = '140px';
        iframeObj.style.width = '195px';
        iframeObj2.style.display = '';
        iframeObj2.style.height = '140px';
        iframeObj2.style.width = '195px';
    }
    setTimeProperties();	
    updateYearDiv();
    updateMonthDiv();
    updateMinuteDiv();
    updateHourDiv();
}

function displayCalendarSelectBox(yearInput,monthInput,dayInput,hourInput,minuteInput,buttonObj)
{
    if(!hourInput)calendarDisplayTime=false; else calendarDisplayTime = true;

    currentMonth = monthInput.options[monthInput.selectedIndex].value/1-1;
    currentYear = yearInput.options[yearInput.selectedIndex].value;
    if(hourInput)
    {
        currentHour = hourInput.options[hourInput.selectedIndex].value;
        inputHour = currentHour/1;
    }
    if(minuteInput)
    {
        currentMinute = minuteInput.options[minuteInput.selectedIndex].value;
        inputMinute = currentMinute/1;
    }

    inputYear = yearInput.options[yearInput.selectedIndex].value;
    inputMonth = monthInput.options[monthInput.selectedIndex].value/1 - 1;
    inputDay = dayInput.options[dayInput.selectedIndex].value/1;

    if(!calendarDiv)
    {
        initCalendar();
    }
    else
    {
        writeCalendarContent();
    }

    returnDateToYear = yearInput;
    returnDateToMonth = monthInput;
    returnDateToDay = dayInput;
    returnDateToHour = hourInput; 	
    returnDateToMinute = minuteInput; 	

    returnFormat = false;
    returnDateTo = false;
    positionCalendar(buttonObj);
    calendarDiv.style.visibility = 'visible';	
    calendarDiv.style.display = 'block';
    if(iframeObj)
    {
    iframeObj.style.display = '';
    iframeObj.style.height = calendarDiv.offsetHeight + 'px';
    iframeObj.style.width = calendarDiv.offsetWidth + 'px';	
    //// fix for EI frame problem on time dropdowns 09/30/2006
    iframeObj2.style.display = '';
    iframeObj2.style.height = calendarDiv.offsetHeight + 'px';
    iframeObj2.style.width = calendarDiv.offsetWidth + 'px'
    }
    setTimeProperties();
    updateYearDiv();
    updateMonthDiv();
    updateHourDiv();
    updateMinuteDiv();
}

// colocar no evento onKeyUp passando o objeto como parametro
function formata_dt(val)
{
    var pass = val.value;
    var expr = /[0123456789]/;

    for (i=0; i<pass.length; i++)
    {
        // charAt -> retorna o caractere posicionado no �ndice especificado
        var lchar = val.value.charAt(i);
        var nchar = val.value.charAt(i+1);

        if (i==0)
        {
            // search -> retorna um valor inteiro, indicando a posi��o do inicio da primeira
            // ocorr�ncia de expReg dentro de instStr. Se nenhuma ocorrencia for encontrada o m�todo retornara -1
            // instStr.search(expReg);
            if ((lchar.search(expr) != 0) || (lchar>3))
            {
                val.value = "";
            }

        }
        else if (i==1)
        {
            if (lchar.search(expr) != 0)
            {
                // substring(indice1,indice2)
                // indice1, indice2 -> ser� usado para delimitar a string
                var tst1 = val.value.substring(0,(i));
                val.value = tst1;
                continue;
            }
            if ((nchar != '/') && (nchar != ''))
            {
                var tst1 = val.value.substring(0, (i)+1);

                if(nchar.search(expr) != 0) var tst2 = val.value.substring(i+2, pass.length);
                else var tst2 = val.value.substring(i+1, pass.length);

                val.value = tst1 + '/' + tst2;
            }
        }
        else if (i==4)
        {
            if (lchar.search(expr) != 0)
            {
                var tst1 = val.value.substring(0, (i));
                val.value = tst1;
                continue;
            }

            if ((nchar != '/') && (nchar != ''))
            {
                var tst1 = val.value.substring(0, (i)+1);
                if(nchar.search(expr) != 0) var tst2 = val.value.substring(i+2, pass.length);
                else var tst2 = val.value.substring(i+1, pass.length);
                val.value = tst1 + '/' + tst2;
            }
        }
        if (i>=6)
        {
            if (lchar.search(expr) != 0)
            {
                var tst1 = val.value.substring(0, (i));
                val.value = tst1;
            }
        }
    }

    if(pass.length==10)
    {
        dia = (val.value.substring(0,2));
        mes = (val.value.substring(3,5));
        ano = (val.value.substring(6,10));

        cons = true;

        // verifica se foram digitados n�meros
        if (isNaN(dia) || isNaN(mes) || isNaN(ano))
        {
            alert("Preencha a data somente com n�meros.");
            val.value = "";
            val.focus();
            return false;
        }

        // verifica o dia valido para cada mes
        if ((dia < "01")||(dia < "01" || dia > "30") && (mes == "04" || mes == "06" ||  mes == "09" || mes == "11" ) ||  dia > "31")
        {
            cons = false;
        }

        // verifica se o mes e valido
        if (mes < "01" || mes > "12" )
        {
            cons = false;
        }

        // verifica se e ano bissexto
        if (mes == "02" && ( dia < "01" || dia > "29" || ( dia > "28" && (parseInt(ano / 4) != ano / 4))))
        {
            cons = false;
        }

        if (cons == false)
        {
            alert("A data inserida n�o � v�lida: " + val.value);
            val.value = "";
            val.focus();
            return false;
        }
    }
    if(pass.length>10)
    val.value = val.value.substring(0, 10);
    return true;
}
/**
* @autor : Ricardo e Rodrigo
* @data : 28/12/2007
* @Objetivo : Validar Mes e ano
* @chamada da fun��o: onKeyUp="formata_mes_ano(this);"
*/
function formata_mes_ano(val)
{
    var pass = val.value;
    var expr = /[0123456789]/;

    for (i=0; i<pass.length; i++)
    {
        // charAt -> retorna o caractere posicionado no �ndice especificado
        var lchar = val.value.charAt(i);
        var nchar = val.value.charAt(i+1);

        if (i==0)
        {
            // search -> retorna um valor inteiro, indicando a posi��o do inicio da primeira
            // ocorr�ncia de expReg dentro de instStr. Se nenhuma ocorrencia for encontrada o m�todo retornara -1
            // instStr.search(expReg);
            if ((lchar.search(expr) != 0) || (lchar>1))
            {
                val.value = "";
            }

        }
        else if (i==1)
        {
            if (val.value.substring(0, 2)>12)
            {
                val.value = val.value.substring(0, (i));
            }
            if (lchar.search(expr) != 0)
            {
                // substring(indice1,indice2)
                // indice1, indice2 -> ser� usado para delimitar a string
                var tst1 = val.value.substring(0,(i));
                val.value = tst1;
                continue;
            }
            if ((nchar != '/') && (nchar != ''))
            {
                var tst1 = val.value.substring(0, (i)+1);//conferir este valor;

                if(nchar.search(expr) != 0) var tst2 = val.value.substring(i+2, pass.length);
                else var tst2 = val.value.substring(i+1, pass.length);

                val.value = tst1 + '/' + tst2;
            }
        }
        if (i>=4)
        {
            if (lchar.search(expr) != 0)
            {
                var tst1 = val.value.substring(0, (i));
                val.value = tst1;
            }
        }
    }

    if(pass.length==7)
    {
        mes = (val.value.substring(0,2));
        ano = (val.value.substring(3,7));

        cons = true;

        // verifica se foram digitados n�meros
        if (isNaN(mes) || isNaN(ano))
        {
            alert("Preencha a data somente com n�meros.");
            val.value = "";
            val.focus();
            return false;
        }

        // verifica se o mes e valido
        if (mes < "01" || mes > "12" )
        {
            cons = false;
        }

        if (cons == false)
        {
            alert("A data inserida n�o � v�lida: " + val.value);
            val.value = "";
            val.focus();
            return false;
        }
    }
    if(pass.length>7)
    val.value = val.value.substring(0, 7);
    return true;
}