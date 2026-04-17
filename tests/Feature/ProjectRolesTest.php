<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectRolesTest extends TestCase
{
    use RefreshDatabase;

    protected function addProjectMember(Project $project, User $user, string $role): void
    {
        $project->members()->attach($user->id, [
            'role' => $role,
        ]);
    }

    public function test_admin_can_create_task(): void
    {
        $admin = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $admin->id,
        ]);

        $this->addProjectMember($project, $admin, 'admin');

        $response = $this->actingAs($admin)->post(route('tasks.store'), [
            'project_id' => $project->id,
            'title' => 'Admin task',
            'description' => 'Created by admin',
            'priority' => 3,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'project_id' => $project->id,
            'title' => 'Admin task',
            'description' => 'Created by admin',
        ]);
    }

    public function test_member_can_create_task(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->addProjectMember($project, $owner, 'admin');
        $this->addProjectMember($project, $member, 'member');

        $response = $this->actingAs($member)->post(route('tasks.store'), [
            'project_id' => $project->id,
            'title' => 'Member task',
            'description' => 'Created by member',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'project_id' => $project->id,
            'title' => 'Member task',
            'description' => 'Created by member',
        ]);
    }

    public function test_viewer_cannot_create_task(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->addProjectMember($project, $owner, 'admin');
        $this->addProjectMember($project, $viewer, 'viewer');

        $response = $this->actingAs($viewer)->post(route('tasks.store'), [
            'project_id' => $project->id,
            'title' => 'Viewer task',
            'description' => 'Should not be created',
        ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('tasks', [
            'project_id' => $project->id,
            'title' => 'Viewer task',
        ]);
    }

    public function test_member_cannot_delete_task(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->addProjectMember($project, $owner, 'admin');
        $this->addProjectMember($project, $member, 'member');

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'title' => 'Task to keep',
        ]);

        $response = $this->actingAs($member)->delete(route('tasks.destroy', $task->id));

        $response->assertForbidden();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
        ]);
    }

    public function test_viewer_cannot_reorder_tasks(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->addProjectMember($project, $owner, 'admin');
        $this->addProjectMember($project, $viewer, 'viewer');

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'status' => 'backlog',
            'order' => 0,
        ]);

        $response = $this->actingAs($viewer)->post(route('tasks.reorder'), [
            'tasks' => [
                [
                    'id' => $task->id,
                    'order' => 0,
                    'status' => 'done',
                ],
            ],
        ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'backlog',
        ]);
    }

    public function test_admin_can_add_project_member(): void
    {
        $admin = User::factory()->create();
        $newUser = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $admin->id,
        ]);

        $this->addProjectMember($project, $admin, 'admin');

        $response = $this->actingAs($admin)->post(route('projects.members.store', $project->id), [
            'email' => $newUser->email,
            'role' => 'viewer',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $newUser->id,
            'role' => 'viewer',
        ]);
    }

    public function test_member_cannot_manage_project_members(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $newUser = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->addProjectMember($project, $owner, 'admin');
        $this->addProjectMember($project, $member, 'member');

        $response = $this->actingAs($member)->post(route('projects.members.store', $project->id), [
            'email' => $newUser->email,
            'role' => 'viewer',
        ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('project_user', [
            'project_id' => $project->id,
            'user_id' => $newUser->id,
        ]);
    }

    public function test_non_member_cannot_open_project(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->addProjectMember($project, $owner, 'admin');

        $response = $this->actingAs($outsider)->get(route('projects.show', $project->id));

        $response->assertNotFound();
    }

    public function test_member_can_comment_task(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->addProjectMember($project, $owner, 'admin');
        $this->addProjectMember($project, $member, 'member');

        $task = Task::factory()->create([
            'project_id' => $project->id,
        ]);

        $response = $this->actingAs($member)->post(route('tasks.comments.store', $task->id), [
            'body' => 'Comment from member',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('task_comments', [
            'task_id' => $task->id,
            'user_id' => $member->id,
            'body' => 'Comment from member',
        ]);
    }

    public function test_viewer_cannot_comment_task(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->addProjectMember($project, $owner, 'admin');
        $this->addProjectMember($project, $viewer, 'viewer');

        $task = Task::factory()->create([
            'project_id' => $project->id,
        ]);

        $response = $this->actingAs($viewer)->post(route('tasks.comments.store', $task->id), [
            'body' => 'Viewer comment',
        ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('task_comments', [
            'task_id' => $task->id,
            'user_id' => $viewer->id,
            'body' => 'Viewer comment',
        ]);
    }

    public function test_member_can_upload_attachment(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->addProjectMember($project, $owner, 'admin');
        $this->addProjectMember($project, $member, 'member');

        $task = Task::factory()->create([
            'project_id' => $project->id,
        ]);

        \Illuminate\Support\Facades\Storage::fake('public');

        $file = \Illuminate\Http\UploadedFile::fake()->create('member-file.txt', 100);

        $response = $this->actingAs($member)->post(route('tasks.attachments.store', $task->id), [
            'file' => $file,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('task_attachments', [
            'task_id' => $task->id,
            'user_id' => $member->id,
            'original_name' => 'member-file.txt',
        ]);
    }

    public function test_viewer_cannot_upload_attachment(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->addProjectMember($project, $owner, 'admin');
        $this->addProjectMember($project, $viewer, 'viewer');

        $task = Task::factory()->create([
            'project_id' => $project->id,
        ]);

        \Illuminate\Support\Facades\Storage::fake('public');

        $file = \Illuminate\Http\UploadedFile::fake()->create('viewer-file.txt', 100);

        $response = $this->actingAs($viewer)->post(route('tasks.attachments.store', $task->id), [
            'file' => $file,
        ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('task_attachments', [
            'task_id' => $task->id,
            'user_id' => $viewer->id,
            'original_name' => 'viewer-file.txt',
        ]);
    }

    public function test_member_cannot_change_task_priority(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->addProjectMember($project, $owner, 'admin');
        $this->addProjectMember($project, $member, 'member');

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'priority' => 1,
        ]);

        $response = $this->actingAs($member)->patch(route('tasks.update', $task->id), [
            'priority' => 3,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'priority' => 1,
        ]);
    }

    public function test_member_cannot_change_task_due_date(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->addProjectMember($project, $owner, 'admin');
        $this->addProjectMember($project, $member, 'member');

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'due_date' => null,
        ]);

        $response = $this->actingAs($member)->patch(route('tasks.update', $task->id), [
            'due_date' => '2030-01-01',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'due_date' => null,
        ]);
    }

    public function test_member_cannot_change_task_assignee(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $otherUser = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->addProjectMember($project, $owner, 'admin');
        $this->addProjectMember($project, $member, 'member');
        $this->addProjectMember($project, $otherUser, 'viewer');

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'user_id' => null,
        ]);

        $response = $this->actingAs($member)->patch(route('tasks.update', $task->id), [
            'user_id' => $otherUser->id,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'user_id' => null,
        ]);
    }

    public function test_admin_can_change_member_role(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->addProjectMember($project, $owner, 'admin');
        $this->addProjectMember($project, $member, 'member');

        $response = $this->actingAs($owner)->patch(
            route('projects.members.update', [$project->id, $member->id]),
            ['role' => 'viewer']
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $member->id,
            'role' => 'viewer',
        ]);
    }

    public function test_admin_can_remove_member(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->addProjectMember($project, $owner, 'admin');
        $this->addProjectMember($project, $member, 'member');

        $response = $this->actingAs($owner)->delete(
            route('projects.members.destroy', [$project->id, $member->id])
        );

        $response->assertRedirect();

        $this->assertDatabaseMissing('project_user', [
            'project_id' => $project->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_admin_cannot_remove_project_owner(): void
    {
        $owner = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->addProjectMember($project, $owner, 'admin');

        $response = $this->actingAs($owner)->delete(
            route('projects.members.destroy', [$project->id, $owner->id])
        );

        $response->assertSessionHasErrors('member');

        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $owner->id,
            'role' => 'admin',
        ]);
    }
}
