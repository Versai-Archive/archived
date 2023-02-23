using Microsoft.AspNetCore.Mvc;
using VersaiData.Models;
using VersaiData.Services;

namespace VersaiAPI.Controllers.smp; 

[ApiController]
[Route("smp/[controller]")]
public class UsersController : ControllerBase {

    private readonly SmpUserService _userService;
    
    public UsersController(IServiceProvider services) {
        _userService = services.GetRequiredService<SmpUserService>();
    }
    
    [HttpGet]
    public async Task<List<SMPUser>> GetAll() =>
        await _userService.GetAll();
    
    [HttpGet("{xuid}")]
    public async Task<ActionResult<SMPUser>> Get(string xuid) {
        var user = await _userService.Get(xuid);

        if (user is null) {
            return NotFound();
        }
        return user;
    }
    
    [HttpPost]
    public async Task<IActionResult> Create(SMPUser arg) {
        var user = await _userService.Get(arg.xuid);

        if (user is not null) { 
            return Conflict();
        }
        
        await _userService.Create(arg);
        return CreatedAtAction(nameof(Get), new { arg.xuid }, user);
    }

    [HttpPut]
    public async Task<IActionResult> Update(SMPUser arg) {
        var user = await _userService.Get(arg.xuid);

        if (user is null) {
            return NotFound();
        }

        await _userService.Update(user);
        return Ok();
    }

}