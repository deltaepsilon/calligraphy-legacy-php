if (!window.cde) {
  window.cde = {};
}

window.cde.trackTransaction = function (transaction) {
  if (!transaction || !location.search.match(/record=true/)) { // Bail if the route doesn't ask for recording
    return;
  }

  var tracker = window._gaq,
    products = transaction.products,
    i = products.length,
    product;

  tracker.push(['_addTrans',
    transaction.id,           // transaction ID - required
    'IStillLoveCalligraphy',  // affiliation or store name
    transaction.amount
  ]);

  while (i--) {
    product = products[i];

    tracker.push(['_addItem',
      transaction.id,           // transaction ID - required
      product.id,           // SKU/code - required
      product.title,        // product name
      product.category,   // category or variation
      product.price,          // unit price - required
      product.quantity               // quantity - required
    ]);
  }

  tracker.push(['_trackTrans']); //submits transaction to the Analytics servers

}
