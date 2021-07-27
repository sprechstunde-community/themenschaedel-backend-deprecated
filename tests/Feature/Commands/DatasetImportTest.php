<?php

namespace Tests\Feature\Commands;

use App\Models\Episode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Mocks\CreatesEpisodeYamlMocks;
use Tests\TestCase;

class DatasetImportTest extends TestCase
{
    use CreatesEpisodeYamlMocks;
    use RefreshDatabase;

    protected const USERNAME = 'johndoe';

    protected $datasetMock;
    protected Episode $episode;

    public function test_import_populates_database()
    {
        $dataset = $this->createYamlMock($this->episode->guid, static::USERNAME);
        $this->populateDatasetMock($dataset);

        $this->artisan('datasets:import ' . $this->getDatasetMockPath())
            ->assertExitCode(0);

        $this->assertNotEmpty($this->episode->refresh()->topics, 'Failed to attach topics to episode');
        $this->assertNotEmpty($this->episode->topics[0]->subtopics, 'Failed to attach subtopics to topics');
    }

    /** @depends test_import_populates_database */
    public function test_import_fails_when_already_imported()
    {
        $dataset = $this->createYamlMock($this->episode->guid, static::USERNAME);
        $this->populateDatasetMock($dataset);

        $this->artisan('datasets:import ' . $this->getDatasetMockPath())
            ->assertExitCode(0);

        $this->artisan('datasets:import ' . $this->getDatasetMockPath())
            ->assertExitCode(1);
    }

    /** @depends test_import_fails_when_already_imported */
    public function test_import_can_skip_fails_when_already_imported()
    {
        $dataset = $this->createYamlMock($this->episode->guid, static::USERNAME);
        $this->populateDatasetMock($dataset);

        $this->artisan('datasets:import ' . $this->getDatasetMockPath())
            ->assertExitCode(0);

        $this->artisan('datasets:import --skip-errors ' . $this->getDatasetMockPath())
            ->assertExitCode(0);
    }

    public function test_import_fails_when_user_missing()
    {
        $dataset = $this->createYamlMock($this->episode->guid, 'username-that-definitely-does-not-exist');
        $this->populateDatasetMock($dataset);

        $this->artisan('datasets:import ' . $this->getDatasetMockPath())
            ->assertExitCode(1);
    }

    /** @depends test_import_fails_when_user_missing */
    public function test_import_can_skip_fails_when_user_missing()
    {
        $dataset = $this->createYamlMock($this->episode->guid, 'username-that-definitely-does-not-exist');
        $this->populateDatasetMock($dataset);

        $this->artisan('datasets:import --skip-errors ' . $this->getDatasetMockPath())
            ->assertExitCode(0);
    }

    public function test_import_fails_when_episode_missing()
    {
        $dataset = $this->createYamlMock('invalid-guid', static::USERNAME);
        $this->populateDatasetMock($dataset);

        $this->artisan('datasets:import ' . $this->getDatasetMockPath())
            ->assertExitCode(1);
    }

    /** @depends test_import_fails_when_episode_missing */
    public function test_import_can_skip_fails_when_episode_missing()
    {
        $dataset = $this->createYamlMock('invalid-guid', static::USERNAME);
        $this->populateDatasetMock($dataset);

        $this->artisan('datasets:import --skip-errors ' . $this->getDatasetMockPath())
            ->assertExitCode(0);
    }

    /** @return string Path to the temporary file containing the dataset. */
    protected function getDatasetMockPath(): string
    {
        return stream_get_meta_data($this->datasetMock)['uri'];
    }

    /** Set content of the dataset file. */
    protected function populateDatasetMock(string $content): void
    {
        fputs($this->datasetMock, $content);
    }

    /** @inheritDoc */
    protected function setUp(): void
    {
        parent::setUp();

        // create episode to attach datasets onto
        $this->episode = Episode::factory()->create();

        // create user to attach datasets onto
        User::factory()->create(['username' => static::USERNAME]);

        // create dataset mock
        $this->datasetMock = tmpfile();
    }

    /** @inheritDoc */
    protected function tearDown(): void
    {
        parent::tearDown();

        // close and therefore remove mock file
        fclose($this->datasetMock);
    }
}
