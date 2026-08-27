let password_match = false;

// Global AJAX error logger — whenever any request to controller.php (or
// elsewhere) fails (500, 404, malformed JSON, etc.), print the raw response
// text to the console so the actual PHP error message is visible instead of
// failing silently.
$(document).ajaxError(function (event, jqxhr, settings, thrownError) {
  console.error(
    "AJAX request failed:", settings.url,
    "\nStatus:", jqxhr.status, thrownError,
    "\nServer response:\n", jqxhr.responseText
  );
});


//ADDING A PRODUCT TOT HE PRODUCT TABLE
function add_product_item() {
  let brand = $("#brand").val();
  let price = $("#price").val();
  let quantity = $("#quantity").val();
  let img_url = $("#img_url").val();
  let category = $("#category").val();
  let description = $("#description").val();

  //SENDING AN AJAX REQUEST
  $.ajax({
    method: "POST",
    url: "../server/controller.php",
    dataType: "json",
    data: {
      type: "add_product_item",
      brand,
      price,
      quantity,
      img_url,
      description,
      category,
    },
    success: (data) => {
      if (data["success"]) {
        $(".success-message").html("Product Added Successfully!");
        $(".success-message").css(
          "animation",
          "animation: show-message 3s ease-in;"
        );
        $("#brand").val("");
        $("#price").val("");
        $("#quantity").val("");
        $("#img_url").val("");
        $("#description").val("");
      } else {
        alert("failed");
      }
    },
  });
}
//GETTING THE USERS INFO IF THEIR LOGGED IN
function get_user_info(location = "") {
  $.ajax({
    method: "POST",
    url: location ? "server/controller.php" : "../server/controller.php",
    dataType: "json",
    data: { type: "get_user_info" },
    success: (data) => {
      if (data["username"] && data["item_count"]) {
        $("#cart_count").html(data["item_count"]);
        let logged_in = `<button onclick="logout(location);" class="btn btn-outline-dark"><i class="fas fa-user-circle"></i> ${data["username"]} | logout</button>`;
        $("#login_btn").html(logged_in);
      }
    },
  });
}

//GETTING THE WISH LIST OF THE USER
function get_wish_list() {
  $.ajax({
    method: "POST",
    url: "../server/controller.php",
    dataType: "json",
    data: { type: "get_wish_list" },
    success: (data) => {
      display_wish_list(data);
    },
  });
}

// DISPLAYING THE WISH LIST OF THE USER
function display_wish_list(data) {
  let html = "";
  for (let index in data) {
    let row = data[index];
    let product_id = row["product_id"];
    let img_url = row["img_url"];
    let brand = row["brand"];
    let description = row["description"];
    let price = row["price"];

    html += `<div class="wish-item">
              <div class="wish-list-img">
                <img src="../${img_url}" alt="image_${product_id}" />
              </div>
              <div>
                <h4>${brand}</h4>
                <p>${description}</p>
                <p>Npr${price}</p>
                <button onclick="add_product(this);wishlist_requests(${product_id}, 'remove_wishlist_item')" product_id="${product_id}" class="btn btn-outline-dark">
                  move to cart
                  <i class="fa fa-cart-shopping" aria-hidden="true"></i>
                </button>
              </div>
            </div>`;
  }
  $(".wish-list").html(html);
}
// LOGIN FUNCTION
function login() {
  let username = $("#username").val();
  let password = $("#password").val();

  $.ajax({
    method: "POST",
    url: "../server/controller.php",
    dataType: "json",
    data: { type: "login", username: username, password: password },
    success: function (data) {
      if (data["success"]) {
        if (data["role"] === "admin") {
          window.location.href = "../admin/dashboard.php";
        } else {
          // If the user was sent here by checkout's login-required redirect,
          // send them back to checkout instead of the homepage.
          let redirectTo = sessionStorage.getItem("kicks_redirect_after_login");
          sessionStorage.removeItem("kicks_redirect_after_login");
          window.location.href = redirectTo || "../index.php";
        }
      } else {
        $("#login-error").text("Incorrect email or password.").show();
      }
    },
  });
}

// Checks login state before sending the user to checkout; if they're not
// logged in, remembers where they were headed and sends them to login first.
function goToCheckout() {
  $.ajax({
    method: "POST",
    url: "../server/controller.php",
    dataType: "json",
    data: { type: "get_user_info" },
    success: function (data) {
      if (data["username"]) {
        window.location.href = "./checkout.php";
      } else {
        sessionStorage.setItem("kicks_redirect_after_login", "../pages/checkout.php");
        window.location.href = "./login.php";
      }
    },
  });
}

//LOGOUT FUNCTION
function logout(location = "") {
  $.ajax({
    method: "POST",
    url: location ? "server/controller.php" : "../server/controller.php",
    dataType: "json",
    data: { type: "logout" },
    success: function (data) {
      window.location.href = location ? "index.php" : "../index.php";
    },
  });
}

//REGISTERING A USER
function register() {
  if (password_match === true) {
    let first_name = $("#first_name").val();
    let last_name = $("#last_name").val();
    let date_of_birth = $("#date_of_birth").val();
    let gender = $("#gender").val();
    let address = $("#address").val();
    let email = $("#email").val();
    let password = $("#password_register").val();

    $.ajax({
      method: "POST",
      url: "../server/controller.php",
      dataType: "json",
      data: {
        type: "register",
        first_name: first_name,
        last_name: last_name,
        date_of_birth: date_of_birth,
        gender: gender,
        address: address,
        email: email,
        password: password,
      },
      success: function (data) {
        if (data["success"]) {
          window.location.href = "login.php";
        } else {
          $("#confirm_password_text")
            .text("Something went wrong! Please try again.")
            .css("color", "red");
        }
      },
    });
  }
}

// CONFIRMING THE PASSWORDS WHEN A USER REGISTERS
function compare_passwords() {
  let password = $("#password_register").val();
  let confirm_password = $("#confirm_password_register").val();
  if (password !== null || confirm_password !== null) {
    if (password !== confirm_password) {
      $("confirm_password_register").css("border", "1px solid red");
      $("#confirm_password_text")
        .text("Passwords don't match!")
        .css("color", "red");
    } else {
      $("confirm_password_register").css("border", "1px solid green");
      $("#confirm_password_text")
        .text("Passwords match!")
        .css("color", "green");
      password_match = true;
    }
  } else {
    $("#confirm_password_text").hide();
  }
}

//GETTING PRODUCTS WHEN THE STORE PAGE LOADS
function get_products() {
  let category = $("#category-filter").val();
  let brand = $("#brands-filter").val();
  let price = $("#price-filter").val();
  let search_input = $("#search-product-input").val();
  let sort_price = $("#sort_price").val();
  $.ajax({
    method: "POST",
    url: "../server/controller.php",
    dataType: "json",
    data: {
      type: "get_products",
      category,
      brand,
      price,
      search_input,
      sort_price,
    },
    success: (data) => {
      if (data["success"]) {
        let filters_array = [category, brand, price, search_input];
        let filters = "";
        filters_array.forEach((element, index) => {
          if (element) {
            if (index == 2) {
              switch (element) {
                case "low":
                  filters += "0Npr - 99Npr > ";
                  break;
                case "medium":
                  filters += "100Npr - 499Npr > ";
                  break;
                case "high":
                  filters += "500Npr & above > ";
                  break;
              }
            } else filters += element + " > ";
          }
        });
        filters = filters.substring(0, filters.length - 2);
        display_products(data, filters);
      }

      get_user_info();
    },
  });
}

//ADDING A PRODUCT TO THE USER'S CART IF IT IS NOT THERE
function add_product(item) {
  let product_id = $(item).attr("product_id");

  // sending an ajax request to create a cart item for the user
  $.ajax({
    method: "POST",
    url: "../server/controller.php",
    dataType: "json",
    data: {
      type: "add_product",
      product_id,
    },
    success: (data) => {
      if (data["success"]) {
        get_products();
        get_cart();
      } else if (data["reason"] === "not_logged_in") {
        // Remember they were on the shop page, send them to log in first.
        sessionStorage.setItem("kicks_redirect_after_login", "../pages/shop.php");
        window.location.href = "./login.php";
      } else if (data["reason"] === "out_of_stock") {
        alert("Sorry, that item just went out of stock.");
        get_products();
      }
    },
  });
}

//GETTING THE CART ITEMS
function get_cart() {
  $.ajax({
    method: "POST",
    url: "../server/controller.php",
    dataType: "json",
    data: { type: "get_cart" },
    success: (data) => {
      display_cart(data);
      get_user_info();
    },
  });
}

// TOTAL AMOUNT OF THE CART HTML
function subtotal_html(data) {
  let html = "";
  let cart_items = data["cart_items"];

  // ADDING THE NUMBER OF ITEMS
  html += `<div class="flex">
            <h3>Cart Total: ${data["total_quantity"]} items</h3>
            <p>price</p>
          </div>
          <div class="order-summary">`;
  for (let index in cart_items) {
    let row = cart_items[index];

    // reading the data from the current row
    let description = row["description"];
    let price = row["price"];
    let quantity = row["cart_item_quantity"];

    html += ` <div class="order-item flex">
                <p>${quantity} ${description} </p>
                <p>Npr ${(quantity * price) / quantity}</p>
              </div>`;
  }
  html += ` </div>
            <div class="flex">
              <h5>Shipping fees</h5>
              <p>10 Npr</p>
            </div>
            <div class="flex">
              <h5>Subtotal</h5>
              <h5>Npr ${data["total_price"]}</h5>
            </div>
            <button onclick='goToCheckout();' class="btn btn-dark py-3">Checkout</button>
            `;
  $(".subtotal").html(html);
}

//DELETE CART ITEM
function delete_cart_item(product_id, request) {
  $.ajax({
    method: "POST",
    url: "../server/controller.php",
    dataType: "json",
    data: {
      type: request,
      product_id,
    },
    success: (data) => {
      if (data["success"]) {
        get_cart();
        get_products();
      }
    },
  });
}

//GOT TO THE SHOP PAGE
const go_to_shop = () => {
  window.location.href = "./shop.php";
};
//DISPLAYING THE CART
function display_cart(data) {
  let html = "";
  let cart_items = data["cart_items"];
  console.log(typeof data["cart_item_quantity"]);
  if (data["total_quantity"] === 0) {
    console.log("Empty cart");
    html += `
      <div class="empty-cart">
        <img src="../images/empty-cart.png"/>
        <div>
          <h2> Seems like your cart is empty</h2>
          <button onclick="go_to_shop();" class="btn btn-dark">Go to Shop</button>
        </div>
      </div>
    `;
    $(".cart").html(html);
    return;
  }
  for (let index in cart_items) {
    let row = cart_items[index];

    // reading the data from the current row
    let product_id = row["product_id"];
    let brand = row["brand"];
    let category = row["category"];
    let price = row["price"];
    let img_url = row["img_url"];
    let quantity = row["cart_item_quantity"];

    html += `<div class="item">
                <div class="item-image flex-column">
                  <img src="../${img_url}" alt="image_${product_id}" />
                </div>
                <div class="info">
                  <h4>${brand}</h4>
                  <div class="flex">
                    <p>Category</p>
                    <p>${category}</p>
                  </div>
                  <div class="flex">
                    <p>Quantity</p>
                    <div class="product-quantity">
                      <button onclick="delete_cart_item(${product_id}, 'decrease_cart_item');" >-</button>
                      <input value=${quantity} type="text" disabled />
                      <button onclick="add_product(this);" product_id="${product_id}">+</button>
                    </div>
                  </div>
                  <div class = "flex">
                    <h3 id="price">Npr${price}</h3>
                    <div>
                      <button onclick="delete_cart_item(${product_id}, 'delete_cart_item')" class="btn btn-outline-warning">
                        <i class="fa fa-trash" aria-hidden="true"></i>
                      </button>
                      <button onclick="wishlist_requests(${product_id},'add_to_wishlist');delete_cart_item(${product_id}, 'delete_cart_item')" class="btn btn-outline-warning">
                        Wishlist <i class="fa fa-heart"></i>
                      </button>
                    </div>
                    
                  </div>
                </div>
              </div>`;
  }
  subtotal_html(data);
  $(".items").html(html);
}

//ADDING A PRODUCT TO THE USER'S WISHLIST IF IT IS NOT THERE
function wishlist_requests(product_id, request) {
  // sending an ajax request to create a wishlist item for the user
  $.ajax({
    method: "POST",
    url: "../server/controller.php",
    dataType: "json",
    data: {
      type: request,
      product_id,
    },
    success: (data) => {
      if (data["success"]) {
        get_products();
        get_wish_list();
      }
    },
  });
}

// Displaying rating
function getRating(rating) {
  let filled = "<i class='fa-solid fa-star' style='color:orange'></i>";
  let empty = "<i class='fa-solid fa-star' style='color:grey'></i>";
  let rating_stars = "";

  let i = 0;
  for (i; i < rating; i++) {
    rating_stars += filled;
  }
  for (i; i < 5; i++) {
    rating_stars += empty;
  }

  return rating_stars;
}

//DISPLAYING THE PRODUCTS AFTER GETTING THEM FROM THE DATABASE
function display_products(data, filters) {
  let html = "";
  let products = data[0];
  window.productMap = window.productMap || {};
  for (let index in products) {
    let row = products[index];

    // reading the data from the current row
    let product_id = row["product_id"];
    let brand = row["brand"];
    let category = row["category"];
    let rating = row["rating"];
    let description = row["description"];
    let price = row["price"];
    let discount_rate = row["discount"];
    let discount = discount_rate ? (price * discount_rate) / 100 : 0;
    let quantity = row["quantity"];
    let img_url = row["img_url"];
    let is_added = row["is_added"];
    let is_wish = row["is_wish"];

    window.productMap[product_id] = row;

    html += `
    <div class="product">
        <div class="product-img">`;
    if (!is_added) {
      if (is_wish) {
        html += `<button onclick="wishlist_requests(${product_id}, 'remove_wishlist_item')" class="add_to_favorite">
                <i style="color:tomato" class="fa-solid fa-heart"></i>
              </button>`;
      } else {
        html += `<button onclick="wishlist_requests(${product_id}, 'add_to_wishlist')" class="add_to_favorite">
                <i class="fa-solid fa-heart"></i>
              </button>`;
      }
    } else {
      html += `<button disabled class="add_to_favorite disabled">
                <i class="fa-solid fa-heart"></i>
              </button>`;
    }

    html += `
        <img onclick="show_product_description(${product_id});" style="cursor: pointer;" src='../${img_url}' alt="product${product_id}" />
        </div>
        <div class="product-info">
            <h3>${brand}</h3>
            <div>${getRating(rating)} (${rating}) </div>
            <p class="mt-3"> ${category} ${
      discount > 0
        ? `<span class="discount badge">Discount -${discount_rate}%</span>`
        : ""
    }</p>`;

    //ADDING THE DISCOUNT IF IT IS GREATER THAN ZERO
    if (discount > 0) {
      html += `<h4>${price - discount}Npr
                  <span style="text-decoration: line-through;color:lightgrey;"> ${price} Npr</span>  
                </h4><h4>`;
    } else {
      html += `<h4> ${price}Npr</h4><h4>`;
    }

    if (quantity > 0) {
      html += `<span style="color: ${quantity < 5 ? "tomato" : "lightgray"}">${
        quantity < 5 ? "only " + quantity + " left!" : "In Stock"
      }</span></h4></div>`;
    } else {
      html += `</h4></div>`;
    }

    // IF THE PRODUCT IS NOT ADD, ADD A BUTTON THAT ALLOWS THE USER TO ADD IT
    if (!is_added)
      html += `<button onclick="add_product(this);wishlist_requests(${product_id}, 'remove_wishlist_item')" product_id="${product_id}" class=" btn btn-outline-dark add_to_cart">Add to cart  <i class="fa-solid fa-cart-shopping"></i></button></div>`;
    else {
      if (quantity == 0)
        html += `<button type="button" class="btn btn-dark" disabled data-bs-toggle="button" autocomplete="off"> out of stock
        </button></div>`;
      else {
        html += `<button type="button" class="btn btn-warning" disabled data-bs-toggle="button" autocomplete="off">added <i class="fa-solid fa-check"></i></button><button onclick="delete_cart_item(${product_id}, 'delete_cart_item')" class="btn btn-outline-warning mx-2">
        Remove from cart <i class="fa fa-trash" aria-hidden="true"></i>
      </button></div>`;
      }
    }
  }
  $("#filter-result").html(filters ? filters : "All Products");
  $(".latest-products").html(html);
}

//DISPLAYING THE DESCRIPTION OF A PRODUCT
function show_product_description(product_id) {
  let row = (window.productMap || {})[product_id];
  if (!row) return;

  let discount_rate = row["discount"];
  let discount = discount_rate ? (row["price"] * discount_rate) / 100 : 0;
  let finalPrice = row["price"] - discount;
  let isAdded = row["is_added"];

  let priceHtml = discount > 0
    ? `<h3 class="mb-0">${finalPrice}Npr <small style="text-decoration:line-through;color:lightgrey;font-size:1rem;">${row["price"]}Npr</small></h3>`
    : `<h3 class="mb-0">${row["price"]}Npr</h3>`;

  let stockHtml = row["quantity"] > 0
    ? `<span style="color:${row["quantity"] < 5 ? "tomato" : "green"};font-size:.85rem;">${row["quantity"] < 5 ? "Only " + row["quantity"] + " left!" : "In stock"}</span>`
    : `<span style="color:tomato;font-size:.85rem;">Out of stock</span>`;

  let footerBtn = isAdded
    ? `<button type="button" class="btn btn-warning" disabled>Already in cart <i class="fa-solid fa-check"></i></button>`
    : row["quantity"] > 0
      ? `<button onclick="add_to_cart_from_modal(${product_id});" class="btn btn-dark px-4">Add to cart <i class="fa-solid fa-cart-shopping"></i></button>`
      : `<button class="btn btn-dark px-4" disabled>Out of stock</button>`;

  let modalHtml = `
  <div class="modal fade" id="product-modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content" style="border-radius:16px;overflow:hidden;">
        <div class="modal-body p-0">
          <div class="row g-0">
            <div class="col-md-6" style="background:rgb(255,218,150);display:flex;align-items:center;justify-content:center;padding:2rem;">
              <img src="../${row["img_url"]}" style="max-height:320px;object-fit:contain;width:auto;" alt="${row["brand"]}">
            </div>
            <div class="col-md-6 p-4 d-flex flex-column">
              <button type="button" class="btn-close align-self-end" data-bs-dismiss="modal"></button>
              <h4 class="mb-1">${row["brand"]}</h4>
              <p style="color:grey;">${row["category"] || ""}</p>
              <p>${row["description"] || ""}</p>
              ${priceHtml}
              <div class="mt-2 mb-3">${stockHtml}</div>
              <div class="mt-auto">${footerBtn}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>`;

  $("#product-modal").remove();
  $("body").append(modalHtml);
  new bootstrap.Modal(document.getElementById("product-modal")).show();
}

// Add-to-cart action used by the product modal (mirrors add_product(), but
// takes a product_id directly instead of reading it off a clicked element).
function add_to_cart_from_modal(product_id) {
  $.ajax({
    method: "POST",
    url: "../server/controller.php",
    dataType: "json",
    data: { type: "add_product", product_id: product_id },
    success: (data) => {
      if (data["success"]) {
        bootstrap.Modal.getInstance(document.getElementById("product-modal"))?.hide();
        get_products();
        get_cart();
      } else if (data["reason"] === "not_logged_in") {
        sessionStorage.setItem("kicks_redirect_after_login", "../pages/shop.php");
        window.location.href = "./login.php";
      } else if (data["reason"] === "out_of_stock") {
        alert("Sorry, that item just went out of stock.");
        get_products();
      }
    },
  });
}