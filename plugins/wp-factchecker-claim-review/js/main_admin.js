(function($, _, Backbone){
var claimReviews = new Backbone.Collection();
var addButton, searchBox, selectedContainer, selectedItemId, itemTemplate, saveNewItemButton, newItemButton;


function setSelected(id){
  if(id){
    addButton.button('enable');
  } else {
    addButton.button('disable');
  }
  selectedItemId = id;
}

function renderSelectedList(){
  selectedContainer.empty();
  var items = claimReviews.where({selected:true});

  items.forEach(function(i){
    var html = itemTemplate(i.toJSON());
    selectedContainer.append(html);
  })

}


function onAddButtonClick(evt){
  event.preventDefault();
  if(!selectedItemId){
    return;
  }
  var obj = claimReviews.get(selectedItemId);
  obj.set('selected',true);

  renderSelectedList();
  searchBox.val('');
}


function getSearchOptions(req, res){
  var options = claimReviews
  .filter(function(itm){
    return !itm.get('selected')
  })
  .filter(function(itm){
    return itm.get('title').indexOf(req.term) != -1;
  })
  .map(function(itm){
    return {
      label: itm.get('title'),
      id: itm.id
    }
  });
  res(options);
}


function resetNewItemForm(){
  $('[name="new-claim-title"]').val('');
  $('[name="claim-quote"]').val('');
  $('[name="claim-summary"]').val('');
  $('[name="author-name"]').val('');
  $('[name="author-type"]:checked').prop('checked', false);
  $('[name="publication-name"]').val('');
  $('[name="publication-url"]').val('');
  $('[name="publication-date"]').val('');
  $('[name="review-summary"]').val('');
  $('[name="review-rating"]:checked').prop('checked', false);
}


function saveNewItem(){
  // gather the data from the form
  var newItem = {};
  newItem.post_title = $('[name="new-claim-title"]').val();
  newItem['claim-quote'] = $('[name="claim-quote"]').val();
  newItem['claim-summary'] = $('[name="claim-summary"]').val();
  newItem['author-name'] = $('[name="author-name"]').val();
  newItem['author-type'] = $('[name="author-type"]:checked').val();
  newItem['publication-name'] = $('[name="publication-name"]').val();
  newItem['publication-url'] = $('[name="publication-url"]').val();
  newItem['publication-date'] = $('[name="publication-date"]').val();
  newItem['review-summary'] = $('[name="review-summary"]').val();
  newItem['review-rating'] = $('[name="review-rating"]:checked').val();
  newItem['new_claim_review_nonce'] = $('[name="new_claim_review_nonce"]').val();
  newItem['claim_review_form_nonce'] = $('[name="claim_review_form_nonce"]').val();


  $.post({
    contentType: 'application/json',
    headers: {
      'X-WP-Nonce': newItem['new_claim_review_nonce']
    },
    url: claimReviewLocaldata.endpoint + '/claim_review',
    data: JSON.stringify(newItem),
    dataType: 'json'
  })
  .done(function(data, status, jqXHR){
      // add item to the list of selected§
      data.selected=true;
      claimReviews.add(data);
      renderSelectedList();
  })
  .fail(function(data, status, jqXHR){
      alert('There was a problem');
  })
  .always(function(){
      $('.newitem-box-content').show();
      resetNewItemForm();
      tb_remove();
  });
  
}

function onSaveNewItemClick(evt){
  evt.preventDefault();
  $('.newitem-box-content').hide().after('saving');
  saveNewItem();

}

function setTBDimensions(){
  newItemButton.attr('href', '#TB_inline?1&width=' + window.innerWidth *.8 + '&height=' + window.innerHeight*.8 + '&inlineId=new-claim-review-box');
}


$(function(){
  addButton = $('#btn-add-selected');
  searchBox = $( "#claim_review_search_q" );
  selectedContainer = $('#claim-review-results-list');
  saveNewItemButton = $('#btn-save-new-claim-review');
  newItemButton = $('#btn-claim-review-add');
  
  setTBDimensions();
  $(window).on('resize', setTBDimensions);

  itemTemplate = _.template($('#tmpl-claim-review-item').html());

  claimReviews.reset(claimsJSON);
  
  
  searchBox
    .autocomplete({
      source: getSearchOptions,
      minLength: 0
    })
    .on('autocompleteselect', function( event, ui ){
      setSelected(ui.item.id);

    })
    .on('autocompletesearch', function(event, ui){
      setSelected(0);
    });
    // $( ".selector" ).on( "autocompleteselect", function( event, ui ) {} );
  
  addButton
    .button()
    .button('disable')
    .click( onAddButtonClick );
  
  saveNewItemButton
  .button()
  .click( onSaveNewItemClick );


  claimReviewLocaldata.selected.forEach(function(selectedItemId){
    var obj = claimReviews.get(selectedItemId);
    obj.set('selected',true);
  });
  renderSelectedList();


})


})(jQuery, _, Backbone);
