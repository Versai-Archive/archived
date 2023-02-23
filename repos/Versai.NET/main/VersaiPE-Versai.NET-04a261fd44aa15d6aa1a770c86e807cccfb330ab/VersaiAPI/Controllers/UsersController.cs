using Microsoft.AspNetCore.Mvc;
using VersaiData.Models;
using VersaiData.Services;

namespace VersaiAPI.Controllers;

[ApiController]
[Route("versai/[controller]")]
public class UsersController : ControllerBase {
    
    private readonly UserService _userService;
    
    public UsersController(IServiceProvider services) {
        // Grab Singletons from depenedency injection that we set in Program.cs
        _userService = services.GetRequiredService<UserService>();
    }
    
    [HttpGet]
    public async Task<List<User>> GetAll() =>
        await _userService.GetAll();
    
    [HttpGet("{xuid}")]
    public async Task<ActionResult<User>> Get(string xuid) {
        var user = await _userService.Get(xuid);

        if (user is null) {
            return NotFound();
        }
        return user;
    }
    
    [HttpPost]
    public async Task<IActionResult> Create(User arg) {
        var user = await _userService.Get(arg.xuid);

        if (user is not null) { 
            return Conflict();
        }
        
        await _userService.Create(arg);
        return CreatedAtAction(nameof(Get), new { xuid = arg.xuid }, user);
    }

    [HttpPut]
    public async Task<IActionResult> Update(User arg) {
        var user = await _userService.Get(arg.xuid);

        if (user is null) {
            return NotFound();
        }
        
        await _userService.Update(user);
        return Ok();
    }
}