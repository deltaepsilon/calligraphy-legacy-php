(function() {
  var cde;

  window.$.fn.center = function() {
    this.css('margin-top', -0.5 * this.outerHeight());
    this.css('margin-left', -0.5 * this.outerWidth());
    return this;
  };

  window.$.fn.centerSides = function() {
    this.css('margin-left', -0.5 * this.outerWidth());
    return this;
  };

  cde = {
    gallery: {
      serialized: ''
    },
    modal: $('#modal'),
    modalInner: $('#modal-inner'),
    modalFunctions: {
      reset: function() {
        cde.modal.hide();
        cde.modal.children('div').hide();
        return cde.modal.children('div.modal-inner').empty();
      },
      show: function(id) {
        var modal, wrapper;
        modal = cde.modal;
        modal.show();
        modal.children('#' + id).show();
        wrapper = function() {
          return modal.one('click', function(e) {
            modal.hide();
            return modal.children().hide();
          });
        };
        setTimeout(wrapper, 200);
        return modal.children().click(function(e) {
          return e.stopPropagation();
        });
      }
    },
    layoutResize: function() {
      return $('html').css('height', $(document).height());
    },
    layout: function() {
      var account, accountDiv, bottomNav, cartDiv, cartTitle, sideNav, targetWidthSideNav;
      accountDiv = $('#account-form');
      account = $('.account');
      account.click(function(e) {
        e.preventDefault();
        cde.modalFunctions.reset();
        accountDiv.show(200, function(e) {
          return accountDiv.find('[type="text"]').first().focus();
        });
        return cde.modalFunctions.show('account-form');
      });
      cartTitle = cde.modal.children('div#cart').children('.cart');
      cartDiv = $('#cart-count');
      if (cartTitle.length) {
        cartTitle.appendTo('#cart-count');
        cartDiv.show();
      } else {
        cartDiv.hide();
      }
      bottomNav = $('nav#bottom-nav');
      if (bottomNav.children('ul').children().length) {
        bottomNav.addClass('open');
        bottomNav.animate({
          bottom: '0'
        });
      }
      bottomNav.children('div.top-border').click(function() {
        var targetHeightBottomNav;
        targetHeightBottomNav = -1 * bottomNav.children('ul').eq(0).height();
        if (parseInt(bottomNav.css('bottom')) < 0) {
          bottomNav.addClass('open');
          return bottomNav.animate({
            bottom: '0'
          });
        } else {
          bottomNav.removeClass('open');
          return bottomNav.animate({
            bottom: targetHeightBottomNav
          });
        }
      });
      sideNav = $('aside#side-nav');
      targetWidthSideNav = -232;
      sideNav.css('left', targetWidthSideNav);
      sideNav.removeClass('side-nav-hidden');
      sideNav.children('div.right-border').click(function() {
        if (parseInt(sideNav.css('left')) < 0) {
          sideNav.addClass('open');
          return sideNav.animate({
            left: '0'
          });
        } else {
          sideNav.removeClass('open');
          return sideNav.animate({
            left: targetWidthSideNav
          });
        }
      });
      return $('input:visible, textarea:visible, select:visible').first().not('.no-focus').focus();
    },
    attributes: function(attributes) {
      var addRow, lastCell, lastDiv, prototype;
      prototype = attributes.attr('data-prototype');
      lastDiv = attributes.children('div').last();
      lastCell = lastDiv.children('input');
      addRow = function(nextNumber) {
        prototype = prototype.replace(/__name__/g, nextNumber);
        prototype = prototype.replace(/\s\sname\s\s/g, 'image');
        attributes.append(prototype);
        return cde.attributes(attributes);
      };
      if (!lastDiv.length) {
        addRow(0);
      }
      return lastCell.one('focus', function() {
        var nextNumber, nextNumberArray, text;
        text = lastDiv.html();
        nextNumberArray = text.match(/\d/);
        nextNumber = parseInt(nextNumberArray[0]) + 1;
        return addRow(nextNumber);
      });
    },
    tableSort: function() {
      return $('.sortable').click(function() {
        var column, rows, sort, table, th;
        th = $(this);
        table = th.parent('tr').parent('thead').parent('table');
        column = table.children('thead').children('tr').children('th').index(th);
        rows = table.children('tbody').children('tr');
        th.siblings().removeClass('desc').removeClass('asc');
        sort = 'desc';
        if (th.hasClass('desc')) {
          th.addClass('asc');
          th.removeClass('desc');
          sort = 'asc';
        } else if (th.hasClass('asc')) {
          th.removeClass('asc');
          th.addClass('desc');
        } else {
          th.addClass('desc');
        }
        rows.each(function(key, row) {
          var date, value;
          row = $(row);
          value = $.trim(row.children('td').eq(column).text());
          if (th.hasClass('date')) {
            date = new Date(value);
            value = date.getTime();
          }
          if (th.hasClass('numeric')) {
            value = parseFloat(value);
          }
          return row.attr('sort', value);
        });
        if (sort === 'asc') {
          rows.sort(function(a, b) {
            if ($(a).attr('sort') > $(b).attr('sort')) {
              return 1;
            }
            if ($(a).attr('sort') < $(b).attr('sort')) {
              return -1;
            }
            return 0;
          });
        } else {
          rows.sort(function(a, b) {
            if ($(a).attr('sort') < $(b).attr('sort')) {
              return 1;
            }
            if ($(a).attr('sort') > $(b).attr('sort')) {
              return -1;
            }
            return 0;
          });
        }
        return table.children('tbody').html(rows);
      });
    },
    gallery: function(element) {
      var gallery, id, route;
      gallery = $(element);
      id = gallery.attr('id');
      route = Routing.generate('CDEUtilityBundle_utility_gallery', {
        name: id
      });
      return $.get(route, function(json) {
        var manifest, placeImage;
        manifest = $.parseJSON(json);
        cde.modalFunctions.reset();
        placeImage = function(url) {
          var image, imageContainer, modalGallery;
          modalGallery = cde.modal.children('#gallery');
          imageContainer = modalGallery.children('div.gallery-image');
          image = imageContainer.children('img');
          image.attr('src', url).load(function() {
            imageContainer.centerSides().css('margin-top', -0.5 * $('.gallery-image').height());
            modalGallery.css('visibility', 'visible');
            return modalGallery.fadeIn();
          });
          modalGallery.show();
          return modalGallery.css('visibility', 'hidden');
        };
        return gallery.click(function(e) {
          var galleryImage, position;
          position = 0;
          e.preventDefault();
          cde.modalFunctions.show(id);
          placeImage(manifest.base + '/' + manifest.contents[position]);
          galleryImage = $('div.gallery-image');
          galleryImage.children('div.back').click(function(e) {
            if (position === 0) {
              position = manifest.contents.length - 1;
            } else {
              position -= 1;
            }
            return placeImage(manifest.base + '/' + manifest.contents[position]);
          });
          return galleryImage.children('div.forward').click(function(e) {
            if (position === manifest.contents.length - 1) {
              position = 0;
            } else {
              position += 1;
            }
            return placeImage(manifest.base + '/' + manifest.contents[position]);
          });
        });
      });
    },
    cart: function() {
      return $('.cart').click(function(e) {
        e.preventDefault();
        return cde.modalFunctions.show('cart');
      });
    },
    userGallery: function() {
      var commentPrototype, count, data, dataRoute, form, formActionPrototype, gallery, galleryHeight, image, left, max, placeImage, position, render, right, route, setComment, setDescription, setUri, timer;
      gallery = $('div.user-gallery');
      galleryHeight = gallery.height();
      image = gallery.children('img');
      left = gallery.children('div.arrow-left-white');
      right = gallery.children('div.arrow-right-white');
      commentPrototype = $('div#comment-prototype').html().trim();
      form = $('div#user-gallery-page form');
      formActionPrototype = form.attr('action');
      route = Routing.generate('CDEUtilityBundle_utility_signed_uri');
      dataRoute = Routing.generate('CDEUtilityBundle_utility_gallery_data');
      data = {};
      count = 0;
      max = 0;
      position = 0;
      timer = 0;
      image.bind('load', function() {
        var imageHeight;
        imageHeight = $(this).outerHeight();
        return $(this).css('margin-top', 0.5 * (galleryHeight - imageHeight)).fadeIn();
      });
      placeImage = function(uri) {
        return image.fadeOut('fast', function() {
          return $(this).attr('src', uri);
        });
      };
      setDescription = function(title, description) {
        var container;
        container = $('div.description');
        container.children('h2').html(title);
        return container.children('p').html(description);
      };
      setUri = function(uriUnsigned) {
        return $.post(route, {
          uri: uriUnsigned
        }, function(result) {
          var jsonResult;
          jsonResult = $.parseJSON(result);
          return placeImage(jsonResult.uri);
        });
      };
      setComment = function(id, comments) {
        var newAction, reAction, reLink;
        $('div.comments').empty();
        $.each(comments, function(key, comment) {
          var date, dateFormatted, day, month, newComment, reComment, reDate, reTitle, year;
          reTitle = /\$\$user\$\$/;
          reDate = /\$\$date\$\$/;
          reComment = /\$\$comment\$\$/;
          date = new Date(comment.created.date.replace(/[A-Z ].+/, ''));
          day = date.getDate();
          month = date.getMonth() + 1;
          year = date.getFullYear();
          dateFormatted = day + '/' + month + '/' + year;
          newComment = commentPrototype.replace(reTitle, comment.user.username).replace(reDate, dateFormatted).replace(reComment, comment.comment);
          if (newComment.length === 0) {
            return $('<h2 />').html('No Comments').appendTo('div.comments');
          } else {
            return $('<div/>', {
              "class": 'comment'
            }).html(newComment).appendTo('div.comments');
          }
        });
        reAction = /999999999/;
        newAction = formActionPrototype.replace(reAction, id);
        form.attr('action', formActionPrototype.replace(reAction, id));
        reLink = /\d+/;
        return $('ul#comment-links li a').each(function() {
          var href, link;
          link = $(this);
          href = link.attr('href');
          return link.attr('href', href.replace(reLink, id));
        });
      };
      render = function(n) {
        var galleryImage, imageMax, imageNumber, wrapper;
        clearTimeout(timer);
        galleryImage = data[n];
        setDescription(galleryImage.title, galleryImage.description);
        setComment(galleryImage.id, galleryImage.comments);
        imageNumber = n + 1;
        imageMax = max + 1;
        $('#gallery-count').text(imageNumber + ' of ' + imageMax);
        wrapper = function() {
          return setUri(galleryImage.filename);
        };
        return timer = setTimeout(wrapper, 500);
      };
      left.click(function() {
        if (position === 0) {
          position = max;
        } else {
          position -= 1;
        }
        return render(position);
      });
      right.click(function() {
        if (position === max) {
          position = 0;
        } else {
          position += 1;
        }
        return render(position);
      });
      return $.post(dataRoute, function(result) {
        var id;
        data = $.parseJSON(result);
        count = data.length;
        max = count - 1;
        if (data.length) {
          id = $('span#image-id').attr('image-id');
          if (id) {
            return $.each(data, function(key, value) {
              if (parseInt(id) === value.id) {
                position = key;
                return render(position);
              }
            });
          } else {
            return render(0);
          }
        }
      });
    }
  };

  $(document).ready(function() {
    var attributes, galleries, userGallery;
    cde.layout();
    cde.tableSort();
    attributes = $('#cde_product_images');
    if (attributes.length) {
      cde.attributes(attributes);
    }
    galleries = $('.gallery');
    if (galleries.length) {
      galleries.each(function(key, value) {
        return cde.gallery(value);
      });
    }
    cde.cart();
    userGallery = $('div.user-gallery');
    if (userGallery.length) {
      cde.userGallery();
    }
    cde.layoutResize();
    return $(window).resize(function() {
      return cde.layoutResize();
    });
  });

}).call(this);
