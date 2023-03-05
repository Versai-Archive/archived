#[macro_use]
extern crate rocket;
use rocket::build;

mod db;
mod endpoints;

use endpoints::hello_world_endpoint;

#[launch]
pub fn index() -> _ {
    build().mount("/", routes![hello_world_endpoint])
}
