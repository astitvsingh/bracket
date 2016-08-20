<?php

use \App\Models\Game;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class GamesTest extends TestCase
{
    use DatabaseMigrations;

    const FACTORY_GAMES_TO_CREATE = 5;

    public function setUp()
    {
        parent::setUp();

        $this->games = factory(App\Models\Game::class,
                               GamesTest::FACTORY_GAMES_TO_CREATE)->create();
    }

    public function testGamesIndexReturnsAListOfGames()
    {
        $this->get('/api/games');

        $this->assertResponseOk();
		$this->seeJsonStructure([
            '*' => [
                'id', 'name', 'short_name', 'slug', 'logo',
                'banner', 'platforms'
            ]
        ]);
	}

    public function testGamesShowReturnsASingleGame()
    {
        $this->get('/api/games/1');

        $this->assertResponseOk();
		$this->seeJsonStructure([
            'id', 'name', 'short_name', 'slug', 'logo',
            'banner', 'platforms'
		]);
	}

    public function testGamesShowReturns404IfResourceDoesntExist()
    {
        $this->get('/api/games/99999');

        $this->assertResponseStatus(404);
        $this->seeJson([
            'message' => 'Game record not found'
        ]);
    }

    public function testGamesDestroyRemovesGameWithId()
    {
        $game = Game::find('1');

        // Assert that game exists before api call
        $this->assertTrue($game instanceof Game);

        // Call delete on the existing Game
        $this->delete('/api/games/1');

        $game = Game::find('1');

        $this->assertResponseOk();
        $this->assertTrue(!$game);
    }

    public function testGamesDestroyReturns404IfResourceDoesntExist()
    {
        $this->delete('/api/games/99999');

        $this->assertResponseStatus(404);
        $this->seeJson([
            'message' => 'Game record not found'
        ]);
    }

    public function testGamesStoreRequiresNameFields()
    {
        $this->actingAs($this->user)->post('/api/games', [
            'name' => ''
        ]);

        $this->assertSessionHasErrors();
    }

    public function testGamesStoreAddsNewGame()
    {
        $this->actingAs($this->user)->post('/api/games', [
            'name' => 'Rocket League',
            'slug' => 'rocket-league',
            'short_name' => 'RL'
        ]);

        $this->assertResponseOk();
        $this->seeJsonStructure(['id']);

        $nextId = GamesTest::FACTORY_GAMES_TO_CREATE + 1;
        $game = Game::find($nextId);

        $this->assertTrue($game instanceof Game);
    }

    public function testGamesUpdateReturns404IfResourceDoesntExist()
    {
        $this->actingAs($this->user)->put('/api/games/99999', []);

        $this->assertResponseStatus(404);
        $this->seeJson([
            'message' => 'Game record not found'
        ]);
    }

    public function testGamesUpdateAllowsUpdatesToAllFields()
    {
        $newName = 'This is an entirely new name.';
        $newShortName = 'this is the new short name.';
        $newSlug = 'this-is-the-new-slug';
        $newLogoPath = '/some/path/to/a/logo.jpg';
        $newBannerPath = '/some/path/to/a/banner.png';

        $resp = $this->actingAs($this->user)->put('/api/games/1', [
            'name' => $newName,
            'short_name' => $newShortName,
            'slug' => $newSlug,
            'logo' => $newLogoPath,
            'banner' => $newBannerPath
        ]);

        $game = Game::find(1);

        $this->assertTrue($game->name === $newName);
        $this->assertTrue($game->short_name === $newShortName);
        $this->assertTrue($game->slug === $newSlug);
        $this->assertTrue($game->logo === $newLogoPath);
        $this->assertTrue($game->banner == $newBannerPath);
    }



}
