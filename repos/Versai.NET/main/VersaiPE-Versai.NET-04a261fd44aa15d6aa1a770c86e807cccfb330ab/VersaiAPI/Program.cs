using VersaiData.Models;
using VersaiData.Services;

var builder = WebApplication.CreateBuilder(args);

var services = builder.Services;
var config = builder.Configuration;

services.AddControllers();

// Learn more about configuring Swagger/OpenAPI at https://aka.ms/aspnetcore/swashbuckle
services.AddEndpointsApiExplorer();
services.AddSwaggerGen();

services.Configure<UserDatabaseSettings>(
    config.GetSection("UsersDatabase"));

services.Configure<UserSmpDatabaseSettings>(
    config.GetSection("SMPUsersDatabase"));

services.AddSingleton<UserService>();
services.AddSingleton<SmpUserService>();

services.AddControllers();

var app = builder.Build();

// Configure the HTTP request pipeline.
if (app.Environment.IsDevelopment()) {
    app.UseSwagger();
    app.UseSwaggerUI();
}

app.UseHttpsRedirection();

app.UseAuthorization();

app.MapControllers();

app.Run();