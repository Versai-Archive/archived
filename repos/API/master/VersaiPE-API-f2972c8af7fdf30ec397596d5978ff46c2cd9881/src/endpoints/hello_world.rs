#[macro_use]
use rocket;

/// GET /hello_world
/// Info: A simple hello world endpoint
#[get("/hello-world")]
pub fn hello_world() -> &'static str {
    "Hello, world!"
}
