## Cart API Documentation

Quick reference for the Cart APIs implemented in this project. These APIs are authenticated and intended to provide a simple persistent cart per user (scoped by vendor).

## Authentication
- All cart endpoints require authentication using Sanctum. Include the user's bearer token in the Authorization header:

  Authorization: Bearer <token>

## Endpoints

### 1) Add to Cart
- Endpoint: `POST /api/cart/add`
- Purpose: Add a product to the authenticated user's cart for the product's vendor. If the same product + options already exists in the user's cart for that vendor, the quantity is incremented.

Request body (JSON):

```json
{
  "product_id": 123,
  "quantity": 2,            // optional, default: 1
  "options": {              // optional - any product options (will be saved as JSON)
    "size": "M",
    "extra": "cheese"
  }
}
```

Validation rules:
- product_id: required, must exist in `products` table
- quantity: optional, integer >= 1
- options: optional, object/array

Successful response (200):

```json
{
  "success": true,
  "cart": {
    "id": 10,
    "user_id": 5,
    "vendor_id": 2,
    "sub_total": 45.50,
    "total": 45.50,
    "items": [
      {
        "id": 44,
        "cart_id": 10,
        "product_id": 123,
        "quantity": 2,
        "price": 22.75,
        "options": {"size":"M","extra":"cheese"},
        "product": { /* product resource */ }
      }
    ]
  }
}
```

Error responses:
- 400: validation fails or product not found. Response has `message` with error text.

Notes:
- Unit price is taken from the product's `sell_price` (or `price` fallback) at time of adding and stored in the cart item as `price`.
- The controller matches existing items by product_id and exact JSON-serialized `options`. If option ordering or structure may vary, normalize keys before calling the API (or the server code can be adjusted to canonicalize options).

### 2) View Cart(s)
- Endpoint: `GET /api/cart`
- Purpose: Return the authenticated user's carts (one per vendor). Optional query parameter `vendor_id` filters to a single vendor cart.

Query parameters:
- `vendor_id` (optional): integer

Response (200): array of cart objects (or empty array):

```json
[
  {
    "id": 10,
    "user_id": 5,
    "vendor_id": 2,
    "sub_total": 45.50,
    "total": 45.50,
    "items": [
      {
        "id": 44,
        "cart_id": 10,
        "product_id": 123,
        "quantity": 2,
        "price": 22.75,
        "options": {"size":"M","extra":"cheese"},
        "product": { /* product resource */ }
      }
    ]
  }
]
```

Errors:
- 401 Unauthorized if the request is not authenticated.

## Implementation notes
- Carts are persisted in the `carts` table (one row per user+vendor). Line items are in `cart_items`.
- Totals are simple: `sub_total` and `total` are recomputed from cart items (taxes/fees/coupons are not applied here). The existing order summary APIs (e.g., `POST /api/general/order/summary`) compute taxes, delivery fees, and coupons.
- Multi-vendor carts: each vendor gets its own cart row. Clients intending to place a multi-vendor order should either merge carts client-side or use the existing multi-vendor ordering flow (the existing RegularOrderService handles multiple vendor orders when passed multiple vendor product lists).

## Common client flow
1. User browses products and adds items via `POST /api/cart/add`.
2. Client shows `/api/cart` grouped by vendor.
3. When user proceeds to checkout for a vendor, client calls the existing order-summary endpoints (e.g., `POST /api/general/order/summary`) with the cart items to calculate delivery, tax, coupons and then places the order via `POST /api/orders`.

## Next steps / recommended additions
- Add endpoints to update quantity, remove an item, and clear a cart. This is small and recommended for a complete cart API.
- Add tests (Feature tests) for add/view/update/remove flows.
- If options canonicalization is required, implement server-side normalization before item matching.

---

File references in the codebase:
- `app/Http/Controllers/API/CartController.php` — controller for the endpoints
- `app/Models/Cart.php` — Cart model
- `app/Models/CartItem.php` — CartItem model
- `database/migrations/*create_carts_table.php` and `*create_cart_items_table.php` — migrations
