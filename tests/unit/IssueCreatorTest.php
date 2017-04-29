<?php

namespace tracker\tests\unit;

use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use tracker\controllers\IssueCreator;
use tracker\enum\IssueStatusEnum;
use tracker\models\Issue;

/**
 * @author Evgeniy Tkachenko <et.coder@gmail.com>
 */
class IssueCreatorTest extends ServiceTest
{
    /** @var IssueCreator */
    protected $service;

    /**
     * @Override
     */
    protected function _before()
    {
        $this->tester->haveFixtures([
            'node' => \tracker\tests\fixtures\IssueFixture::class,
            'space' => \humhub\modules\space\tests\codeception\fixtures\SpaceFixture::class,
        ]);

        $this->service = new IssueCreator();

        \Yii::$app->user->switchIdentity(User::findOne(['id' => 1]));
    }

    public function testCreateDraftIssue()
    {
        $this->tester->dontSeeRecord(Issue::class, ['id' => 1]);

        $draftIssueModel = $this->createDraft();

        $this->assertEquals(1, $this->service->getIssueForm()->id);
        $this->assertEquals(1, $draftIssueModel->id);
        $this->assertEquals(IssueStatusEnum::TYPE_DRAFT, $draftIssueModel->status);
        $this->tester->seeRecord(Issue::class, ['id' => 1, 'status' => IssueStatusEnum::TYPE_DRAFT]);
    }

    public function testCreateNewIssue()
    {
        $this->createDraft();
        $this->assertFalse($this->service->create());

        $this->service->load(['title' => 'My test issue'], '');

        $requiredAttributesIssue = [
            'id' => 1,
            'title' => 'My test issue',
            'status' => IssueStatusEnum::TYPE_WORK,
        ];

        $this->tester->dontSeeRecord(Issue::class, $requiredAttributesIssue);
        $this->assertInstanceOf(Issue::class, $this->service->create());
        $this->tester->seeRecord(Issue::class, $requiredAttributesIssue);
    }

    private function createDraft()
    {
        $spaceContent = Space::findOne(['id' => 1]);
        return $this->service->createDraft($spaceContent);
    }
}
