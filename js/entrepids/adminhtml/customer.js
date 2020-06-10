
function is3ACustomer(){
    if($('_accountgroup_id')){
        return /AAA$/.test($('_accountgroup_id').options[$('_accountgroup_id').selectedIndex].text);
    }else{
        return false;
    }
}

document.observe("dom:loaded", function() {
  if(is3ACustomer()){
      $('_accountrfc').addClassName('required-entry');
      $$('label[for="_accountrfc"]').each(function(obj){ obj.replace('<label for="_accountrfc">RFC <span class="required">*</span></label>'); });
  }
  if($('_accountgroup_id')){
    Event.observe($('_accountgroup_id'),'change', function(){
        if($('advice-required-entry-_accountrfc')){
            $('advice-required-entry-_accountrfc').remove();
        }
        if(is3ACustomer()){
            $('_accountrfc').addClassName('required-entry');
            $$('label[for="_accountrfc"]').each(function(obj){ obj.replace('<label for="_accountrfc">RFC <span class="required">*</span></label>'); });
        }else{
            $('_accountrfc').removeClassName('required-entry');
            $$('label[for="_accountrfc"]').each(function(obj){ obj.replace('<label for="_accountrfc">RFC</label>'); });
        }
    });
  }
});