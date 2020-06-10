/**
 * @category   Entrepids
 * @package    Entrepids_Cronscheduler
 * @author     miguel.perez@entrepids.com
 * @website    http://www.entrepids.com
 */

var crontab = {
    cronExpression:[],
    minutesPattern: '^([0-9]|[1-5][0-9])$',
    hoursPattern: '^([0-9]|1[0-9]|2[0-3])$',
    daysPattern: '^([0-9]|[1-2][0-9]|3[0-1])$',
    monthsPattern: '^([1-9]|1[0-2])$',
    weekdaysPattern: '^([0-6])$',
    hideAll: function () {
        $('yearly_months').parentNode.parentNode.hide();
        $('monthly_days').parentNode.parentNode.hide();
        $('weekly_days').parentNode.parentNode.hide();
        $('hours_range').parentNode.parentNode.hide();
        $('a_hour').parentNode.parentNode.hide();
        $('minutes_range').parentNode.parentNode.hide();
        $('a_minute').parentNode.parentNode.hide();
    },
    showMinutesFields: function () {
        $('minutes_range').parentNode.parentNode.show();
    },
    showHoursFields: function () {
        $('a_minute').parentNode.parentNode.show();
        $('hours_range').parentNode.parentNode.show();
    },
    showDailyFields: function () {
        $('a_minute').parentNode.parentNode.show();
        $('a_hour').parentNode.parentNode.show();
    },
    showWeeklyFields: function () {
        $('weekly_days').parentNode.parentNode.show();
        $('a_minute').parentNode.parentNode.show();
        $('a_hour').parentNode.parentNode.show();
    },
    showMonhtlyFields: function () {
        $('monthly_days').parentNode.parentNode.show();
        $('weekly_days').parentNode.parentNode.show();
        $('a_minute').parentNode.parentNode.show();
        $('a_hour').parentNode.parentNode.show();
    },
    showYearlyFields: function () {
        $('yearly_months').parentNode.parentNode.show();
        $('monthly_days').parentNode.parentNode.show();
        $('weekly_days').parentNode.parentNode.show();
        $('a_minute').parentNode.parentNode.show();
        $('a_hour').parentNode.parentNode.show();
    },
    getVisibleItems: function () {
        var currentFrequency = $('cron_frequency').getValue();
        this.hideAll();
        switch (parseInt(currentFrequency)) {
            case 0, 1:
                break;
            case 2:
                this.showMinutesFields();
                break;
            case 3:
                this.showHoursFields();
                break;
            case 4:
                this.showDailyFields();
                break;
            case 5:
                this.showWeeklyFields();
                break;
            case 6:
                this.showMonhtlyFields();
                break;
            case 7:
                this.showYearlyFields();
                break;
            default:
                break;
        }
    },
    getCurrentCronSetting: function () {
        var currentExp = $('schedule_cron_expr').getValue();
        if (currentExp === '* * * * *' || currentExp === 'always') {
            this.setCurrentOption(1);
        } else if (currentExp.length > 0) {
            var currentOpt = this.getCurrentOptionFromData(currentExp);
            this.setCurrentOption(currentOpt);
        } else {
            this.setCurrentOption(0);
        }
    },
    setCurrentOption: function (value) {
        $('cron_frequency').setValue(value);
        this.getVisibleItems();
    },
    getCurrentOptionFromData: function (cronexp) {
        var c = cronexp.split(' ');
        var currentOpt = 1;
        var minutesOptions = [];
        var hoursOptions = [];
        var daysOptions = [];
        var monthsOptions = [];
        var weekdaysOptions = [];
        if (c.length === 5) {
            for (i = 0; i < c.length; i++) {
                this.cronExpression[i] = c[i];
                if (c[i] !== '*') {
                    switch (i) {
                        case 0: //minutes
                            minutesOptions = this.getByPatternValues(c[i], this.minutesPattern);
                            if (minutesOptions.length > 1) {
                                $('minutes_range').setValue(minutesOptions);
                            } else {
                                $('minutes_range').setValue(minutesOptions);
                                $('a_minute').setValue(minutesOptions[0]);
                            }
                            currentOpt = 2; //Minutes
                            break;
                        case 1: //hours
                            hoursOptions = this.getByPatternValues(c[i], this.hoursPattern);
                            if (hoursOptions.length > 1) {
                                $('hours_range').setValue(hoursOptions);
                            } else {
                                $('hours_range').setValue(hoursOptions);
                                $('a_hour').setValue(hoursOptions[0]);
                            }
                            if (hoursOptions.length === 1 && minutesOptions.length === 1) {
                                currentOpt = 4; //Daily
                            } else {
                                currentOpt = 3; //Hours
                            }
                            break;
                        case 2: //monhtly
                            daysOptions = this.getByPatternValues(c[i], this.daysPattern);
                            $('monthly_days').setValue(daysOptions);
                            currentOpt = 6; //Mohntly
                            break;
                        case 3: //Yearly
                            monthsOptions = this.getByPatternValues(c[i], this.monthsPattern);
                            console.log(monthsOptions);
                            $('yearly_months').setValue(monthsOptions);
                            currentOpt = 7; //Yearlys
                            break;
                        case 4: //Yearly
                            weekdaysOptions = this.getByPatternValues(c[i], this.weekdaysPattern);
                            $('weekly_days').setValue(weekdaysOptions);
                            if (currentOpt < 5) {
                                currentOpt = 5; //Weekly
                            }
                            break;
                        default: //NPI
                            break;
                    }
                }
            }
            return currentOpt;
        } else {
            return 0;
        }
    },
    getByPatternValues: function (exp, pattern) {
        var regex = new RegExp(pattern);
        var arrayOptions = [];
        if (regex.test(exp)) { //If is a valid minute
            arrayOptions.push(parseInt(exp));
        } else if (exp.includes('*/')) { // If is valid sequence of numbers
            var values = exp.split('/');
            if ((typeof values[1] !== 'undefined') && regex.test(values[1])) {
                var mult = parseInt(values[1]);
                for (var i = 0; i < 60; i = i + mult) {
                    arrayOptions.push(i);
                }
            }
        } else if (exp.includes(',')) { // If is valid sequence of numbers
            var values = exp.split(',');
            for (i = 0; i < values.length; i++) {
                if (regex.test(values[i])) {
                    arrayOptions.push(parseInt(values[i]));
                }
            }
        }
        return arrayOptions;
    },
    generateMinutesExpression:function(currentOpt){
        e = '*';
        switch(currentOpt){
            case 2:
                var selectedValues = $('minutes_range').getValue();
                if(selectedValues.length<60 && selectedValues.length>1){
                    e = this.getSequence(60,selectedValues);
                }else if(selectedValues.length===1){
                    e = selectedValues[0];
                }
                break;
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:
                var selectedValue = $('a_minute').getValue();
                e = selectedValue;
                break;
            default:
                e = '*';
                break;
        }
        return e;
    },
    generateHoursExpression:function(currentOpt){
        e = '*';
        switch(currentOpt){
            case 3:
                var selectedValues = $('hours_range').getValue();
                if(selectedValues.length<24 && selectedValues.length>1){
                    e = this.getSequence(24,selectedValues);
                }else if(selectedValues.length===1){
                    e = selectedValues[0];
                }
                break;
            case 4:
            case 5:
            case 6:
            case 7:
                var selectedValue = $('a_hour').getValue();
                e = selectedValue;
                break;
            default:
                e = '*';
                break;
        }
        return e;
    },
    generateWeekdayExpression:function(currentOpt){
        e = '*';
        switch(currentOpt){
            case 5:
            case 6:
            case 7: 
                var selectedValues = $('weekly_days').getValue();
                if(selectedValues.length<7 && selectedValues.length>1){
                    e = this.getSequence(7,selectedValues);
                }else if(selectedValues.length===1){
                    e = selectedValues[0];
                }
                break;
            default:
                e = '*';
                break;
        }
        return e;
    },
    generateDaysExpression:function(currentOpt){
        e = '*';
        switch(currentOpt){
            case 6:
            case 7:
                var selectedValues = $('monthly_days').getValue();
                if(selectedValues.length<31 && selectedValues.length>1){
                    e = this.getSequence(31,selectedValues);
                }else if(selectedValues.length===1){
                    e = selectedValues[0];
                }
                break;
            default:
                e = '*';
                break;
        }
        return e;
    },
    generateMonthsExpression:function(currentOpt){
        e = '*';
        switch(currentOpt){
            case 7:        
                var selectedValues = $('yearly_months').getValue();
                if(selectedValues.length<12 && selectedValues.length>1){
                    e = this.getSequence(12,selectedValues);
                }else if(selectedValues.length===1){
                    e = selectedValues[0];
                }
                break;
            default:
                e = '*';
                break;
        }
        return e;
    },
    getSequence: function(totalItems,selectedItems){
        var isSeq = false;
        var z = totalItems % selectedItems.length;
        if(z === 0){
            var seq = totalItems/selectedItems.length;
            for(var i=1; i<selectedItems.length; i++){
                var m = seq*i;
                var n = parseInt(selectedItems[i]);
                if(m === n){
                    isSeq = true;
                }else{
                    isSeq = false;
                    break;
                }
            }if(isSeq){
                return '*/'+seq;
            }else{
                return selectedItems.join(',');
            }
        }else{
            return selectedItems.join(',');
        }
    },
    generateCronExpression: function(){
        var currentFrequency = parseInt($('cron_frequency').getValue());
        this.cronExpression = ['*','*','*','*','*'];
        switch(currentFrequency){
            case 0: 
                this.cronExpression = [];
                break;
            case 1:
                break;
            case 2:
                var minutesExp = this.generateMinutesExpression(currentFrequency);
                this.cronExpression[0] = minutesExp;
                break;
            case 3:
                var minutesExp = this.generateMinutesExpression(currentFrequency);
                var hoursExp = this.generateHoursExpression(currentFrequency);
                this.cronExpression[0] = minutesExp;
                this.cronExpression[1] = hoursExp;
                break;
            case 4:
                var minutesExp = this.generateMinutesExpression(currentFrequency);
                var hoursExp = this.generateHoursExpression(currentFrequency);
                this.cronExpression[0] = minutesExp;
                this.cronExpression[1] = hoursExp;
                break;
            case 5:
                var minutesExp = this.generateMinutesExpression(currentFrequency);
                var hoursExp = this.generateHoursExpression(currentFrequency);
                var weekdayExp = this.generateWeekdayExpression(currentFrequency);
                this.cronExpression[0] = minutesExp;
                this.cronExpression[1] = hoursExp;
                this.cronExpression[4] = weekdayExp;
                break;
            case 6:
                var minutesExp = this.generateMinutesExpression(currentFrequency);
                var hoursExp = this.generateHoursExpression(currentFrequency);
                var weekdayExp = this.generateWeekdayExpression(currentFrequency);
                var daysExp = this.generateDaysExpression(currentFrequency);
                this.cronExpression[0] = minutesExp;
                this.cronExpression[1] = hoursExp;
                this.cronExpression[4] = weekdayExp;
                this.cronExpression[2] = daysExp;
                break;
            case 7:
                var minutesExp = this.generateMinutesExpression(currentFrequency);
                var hoursExp = this.generateHoursExpression(currentFrequency);
                var weekdayExp = this.generateWeekdayExpression(currentFrequency);
                var daysExp = this.generateDaysExpression(currentFrequency);
                var MonthsExp = this.generateMonthsExpression(currentFrequency);
                this.cronExpression[0] = minutesExp;
                this.cronExpression[1] = hoursExp;
                this.cronExpression[4] = weekdayExp;
                this.cronExpression[2] = daysExp;
                this.cronExpression[3] = MonthsExp;
                break;
            default:
                this.cronExpression = [];
                break;
        }
        var ce = '';
        if(this.cronExpression.length > 0){
            ce = this.cronExpression.join(' ');
        }
        $('schedule_cron_expr').setValue(ce);
    },
    generateCronFrequency: function(field,limit){
        var selectedValue = field.getValue();
        var newValues= [];
        if(typeof selectedValue[0] !== 'undefined'){
            var initVal = parseInt(selectedValue[0]);
            for(i=0;i<limit;i++){
                var v = i * initVal;
                newValues.push(v);
            }
        }
        field.setValue(newValues);
    }

};
document.observe("dom:loaded", function () {
    crontab.hideAll();
    crontab.getCurrentCronSetting();
    $('cron_frequency').observe('change', function () {
        crontab.getVisibleItems();
        crontab.generateCronExpression();
    });
    $('yearly_months').observe('change', function () { 
        crontab.generateCronExpression();
    });
    $('monthly_days').observe('change', function () { 
        crontab.generateCronExpression();
    });
    $('weekly_days').observe('change', function () { 
        crontab.generateCronExpression();
    });
    $('hours_range').observe('change', function () { 
        crontab.generateCronExpression();
    });
    $('a_hour').observe('change', function () { 
        crontab.generateCronExpression();
    });
    $('minutes_range').observe('change', function () { 
        crontab.generateCronExpression();
    });
    $('a_minute').observe('change', function () { 
        crontab.generateCronExpression();
    });
    $('minutes_range').observe('dblclick', function () { 
        crontab.generateCronFrequency(this,60);
        crontab.generateCronExpression();
    });
    $('hours_range').observe('dblclick', function () { 
        crontab.generateCronFrequency(this,24);
        crontab.generateCronExpression();
    });
    $('monthly_days').observe('dblclick', function () { 
        crontab.generateCronFrequency(this,31);
        crontab.generateCronExpression();
    });
    
});


