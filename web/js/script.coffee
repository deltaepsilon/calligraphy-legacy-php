# Extend JQuery
window.$.fn.center = ->
  this.css 'margin-top', -0.5 * this.outerHeight()
  this.css 'margin-left', -0.5 * this.outerWidth()
  return this
window.$.fn.centerSides = ->
  this.css 'margin-left', -0.5 * this.outerWidth()
  return this
cde =
  gallery:
    serialized: ''
  modal: $('#modal')
  modalInner: $('#modal-inner')
  modalFunctions:
    reset: ->
      cde.modal.hide()
      cde.modal.children('div').hide()
      cde.modal.children('div.modal-inner').empty()
    show: (id) ->
      modal = cde.modal
      modal.show()
      modal.children('#' + id).show()
      wrapper = ->
        modal.one 'click', (e) ->
          modal.hide()
          modal.children().hide()
      setTimeout wrapper, 200
      modal.children().click (e) ->
        e.stopPropagation()
  layoutResize: ->
    $('html').css 'height', $(document).height()
  layout: ->
    accountDiv = $('#account-form')
    account = $('.account')
    account.click (e) ->
      e.preventDefault()
      cde.modalFunctions.reset()
      accountDiv.show 200, (e) ->
        accountDiv.find('[type="text"]').first().focus()
      cde.modalFunctions.show('account-form')
    cartTitle = cde.modal.children('div#cart').children('.cart')
    cartDiv = $('#cart-count')
    if cartTitle.length
      cartTitle.appendTo('#cart-count')
      cartDiv.show()
    else
      cartDiv.hide()

    # Open bottom nav if it has items, enable click-to-toggle
    bottomNav = $('nav#bottom-nav')
    if bottomNav.children('ul').children().length
      bottomNav.addClass('open')
      bottomNav.animate
        bottom: '0'
    bottomNav.children('div.top-border').click ->
      targetHeightBottomNav = -1 * bottomNav.children('ul').eq(0).height()
      if parseInt(bottomNav.css('bottom')) < 0
        bottomNav.addClass('open')
        bottomNav.animate
          bottom: '0'
      else
        bottomNav.removeClass('open')
        bottomNav.animate
          bottom: targetHeightBottomNav
    # Side nav defaults to open, so toggle it if clicked
    sideNav = $('aside#side-nav')
#    targetWidthSideNav = -1 * ( sideNav.outerWidth() + 2 )
#    I give up.  Different browsers calculate outerWidth differently at load
    targetWidthSideNav = -232;
    sideNav.css 'left', targetWidthSideNav
    sideNav.removeClass('side-nav-hidden')
    sideNav.children('div.right-border').click ->
      if parseInt(sideNav.css('left')) < 0
        sideNav.addClass('open')
        sideNav.animate
          left: '0'
      else
        sideNav.removeClass('open')
        sideNav.animate
          left: targetWidthSideNav
#   Try to focus on the first input, textarea or select if available.  It should make forms a little faster to travers.
    $('input:visible, textarea:visible, select:visible').first().not('.no-focus').focus()
  attributes: (attributes)->
    prototype = attributes.attr 'data-prototype'
    lastDiv = attributes.children('div').last()
    lastCell = lastDiv.children('input')
    addRow = (nextNumber) ->
      prototype = prototype.replace /__name__/g, nextNumber
      prototype = prototype.replace /\s\sname\s\s/g, 'image'
      attributes.append prototype
      cde.attributes attributes
    if !lastDiv.length
      addRow 0
    lastCell.one 'focus', ->
      text = lastDiv.html()
      nextNumberArray = text.match /\d/
      nextNumber = parseInt(nextNumberArray[0]) + 1
      addRow nextNumber
  tableSort: ->
    $('.sortable').click ->
      th = $(this)
      table = th.parent('tr').parent('thead').parent 'table'
      column = table.children('thead').children('tr').children('th').index th
      rows = table.children('tbody').children 'tr'
      th.siblings().removeClass('desc').removeClass('asc')
      sort = 'desc'
      if th.hasClass 'desc'
        th.addClass 'asc'
        th.removeClass 'desc'
        sort = 'asc'
      else if th.hasClass 'asc'
        th.removeClass 'asc'
        th.addClass 'desc'
      else
        th.addClass 'desc'
      rows.each (key, row) ->
        row = $(row)
        value = $.trim row.children('td').eq(column).text()
        if th.hasClass 'date'
          date = new Date value
          value = date.getTime()
        if th.hasClass 'numeric'
          value = parseFloat value
        row.attr 'sort', value
      if sort == 'asc'
        rows.sort (a, b) ->
          if $(a).attr('sort') > $(b).attr('sort')
            return 1
          if $(a).attr('sort') < $(b).attr('sort')
              return -1
          return 0
      else
        rows.sort (a, b) ->
          if $(a).attr('sort') < $(b).attr('sort')
            return 1
          if $(a).attr('sort') > $(b).attr('sort')
            return -1
          return 0        
      table.children('tbody').html(rows)
  gallery: (element) ->
    gallery = $(element);
    id = gallery.attr('id')
    route = Routing.generate 'CDEUtilityBundle_utility_gallery',
      name: id
    $.get route, (json) ->
      manifest = $.parseJSON json
      cde.modalFunctions.reset()
      placeImage = (url) ->
        modalGallery = cde.modal.children('#gallery')
        imageContainer = modalGallery.children('div.gallery-image')
        image = imageContainer.children('img')
        image.attr('src', url).load ->
#          imageContainer.centerSides().css 'margin-top', $(window).height() / 5
          imageContainer.centerSides().css 'margin-top', -0.5 * $('.gallery-image').height()
          modalGallery.css('visibility', 'visible')
          modalGallery.fadeIn()
        modalGallery.show()
        modalGallery.css('visibility', 'hidden')
      gallery.click (e) ->
        position = 0
        e.preventDefault()
        cde.modalFunctions.show id
        placeImage manifest.base + '/' + manifest.contents[position]
        galleryImage = $('div.gallery-image')
        galleryImage.children('div.back').click (e) ->
          if position == 0
            position = manifest.contents.length - 1
          else 
            position -= 1
          placeImage manifest.base + '/' + manifest.contents[position]
        galleryImage.children('div.forward').click (e) ->
          if position == manifest.contents.length - 1
            position = 0
          else 
            position += 1
          placeImage manifest.base + '/' + manifest.contents[position]
  cart: ->
    $('.cart').click (e) ->
      e.preventDefault()
      cde.modalFunctions.show('cart')
  userGallery: ->
    gallery = $('div.user-gallery')
    galleryHeight = gallery.height()
    image = gallery.children('img')
    left = gallery.children('div.arrow-left-white')
    right = gallery.children('div.arrow-right-white')
    commentPrototype = $('div#comment-prototype').html().trim()
    form = $('div#user-gallery-page form')
    formActionPrototype = form.attr('action')
    route = Routing.generate 'CDEUtilityBundle_utility_signed_uri'
    dataRoute = Routing.generate 'CDEUtilityBundle_utility_gallery_data'
    data = {}
    count = 0
    max = 0
    position = 0
    timer = 0
    image.bind 'load', ->
      imageHeight = $(this).outerHeight()
      $(this).css('margin-top', 0.5 * ( galleryHeight - imageHeight )).fadeIn()
    placeImage = (uri) ->
      image.fadeOut 'fast', ->
        $(this).attr('src', uri)
    setDescription = (title, description) ->
      container = $('div.description')
      container.children('h2').html title
      container.children('p').html description
    setUri = (uriUnsigned) ->
      $.post route,
        uri: uriUnsigned
      , (result) ->
        jsonResult = $.parseJSON result
        placeImage jsonResult.uri
    setComment = (id, comments) ->
      $('div.comments').empty()
      $.each comments, (key, comment) ->
        reTitle = /\$\$user\$\$/
        reDate = /\$\$date\$\$/
        reComment = /\$\$comment\$\$/
        date = new Date(comment.created.date.replace(/[A-Z ].+/, ''))
        day = date.getDate()
        month = date.getMonth() + 1
        year = date.getFullYear()
        dateFormatted = day + '/' + month + '/' + year
        newComment = commentPrototype.replace(reTitle, comment.user.username).replace(reDate, dateFormatted).replace(reComment, comment.comment)
        if newComment.length == 0
          $('<h2 />').html('No Comments').appendTo('div.comments')
        else
          $('<div/>',
            class: 'comment'
          ).html(newComment).appendTo('div.comments')
      reAction = /999999999/
      newAction = formActionPrototype.replace(reAction, id)
      form.attr 'action', formActionPrototype.replace(reAction, id)
      reLink = /\d+/
      $('ul#comment-links li a').each ->
        link = $(this)
        href = link.attr 'href'
        link.attr 'href', href.replace reLink, id
    render = (n) ->
      clearTimeout timer
      galleryImage = data[n]
      setDescription galleryImage.title, galleryImage.description
      setComment galleryImage.id, galleryImage.comments
      imageNumber = n + 1
      imageMax = max + 1
      $('#gallery-count').text imageNumber + ' of ' + imageMax
      wrapper = ->
        setUri galleryImage.filename
      timer = setTimeout wrapper, 500
    left.click ->
      if position == 0
        position = max
      else
        position -= 1
      render position
    right.click ->
      if position == max
        position = 0
      else
        position += 1
      render position
    # Get data
    $.post dataRoute, (result) ->
      data = $.parseJSON result
      count = data.length
      max = count - 1
      if data.length
        id = $('span#image-id').attr 'image-id'
        if id
          $.each data, (key, value) ->
            if parseInt(id) == value.id
              position = key
              render position
        else
          render 0
$(document).ready ->
  cde.layout()
  cde.tableSort()
  attributes = $('#cde_product_images')
  if attributes.length
    cde.attributes attributes
  galleries = $('.gallery')
  if galleries.length
    galleries.each (key, value) ->
      cde.gallery(value)
  cde.cart()
  userGallery = $('div.user-gallery')
  if userGallery.length
    cde.userGallery()
  cde.layoutResize()
  $(window).resize ->
    cde.layoutResize()
