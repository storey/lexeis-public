import { NgModule } from '@angular/core';
import { Router, NavigationEnd, RouterModule, Routes, ActivatedRoute } from '@angular/router';

import { filter, map, mergeMap} from "rxjs/operators";
import { Title } from '@angular/platform-browser';

import { MainComponent } from './main/main.component';
import { EntrySearchComponent } from './search/entry-search.component';
import { BetacodeComponent } from './betacode/betacode.component';

import { EntryComponent } from './entry/entry.component';
import { RootGroupComponent } from './root-group/root-group.component';
import { CompoundComponent } from './compound/compound.component';
import { SemanticGroupComponent } from './semantic-group/semantic-group.component';

import { BrowseComponent } from './browse/browse.component';

import { TextComponent } from './text/text.component';

import { AboutComponent } from './about/about.component';

import { UserComponent } from './user/user.component';
import { LoginComponent } from './login/login.component';
import { AccessDeniedComponent } from './access-denied/access-denied.component';

import { DefBuilderSearchComponent } from './tools/article-builder/def-builder-search.component';
import { DefBuilderComponent } from './tools/article-builder/def-builder.component';
import { ChangeLogComponent } from './tools/changelog/changelog.component';
import { UnwrittenArticlesComponent } from './tools/unwritten-articles/unwritten-articles.component';
import { AssignedArticlesComponent } from './tools/unwritten-articles/assigned-articles.component';
import { SubmittedDraftsComponent } from './tools/article-drafts/submitted-drafts.component';
import { UserDraftsComponent } from './tools/article-drafts/user-drafts.component';
import { ViewArticleComponent } from './tools/article-drafts/view-article-draft.component';
import { ReviewEntriesComponent } from './tools/review-entries/review-entries.component';
import { IssuesListComponent } from './tools/reported-issues/issues-list.component';
import { ViewIssueComponent } from './tools/reported-issues/view-issue.component';
import { ReportIssuePageComponent } from './tools/reported-issues/report-issue-page.component';
import { InconsistenciesComponent } from './tools/inconsistencies/inconsistencies.component';
import { InvalidArticleRefsComponent } from './tools/inconsistencies/invalid-article-refs.component';
import { NonexistentLemmataComponent } from './tools/inconsistencies/nonexistent-lemmata.component';
import { ZeroRefLemmataComponent } from './tools/inconsistencies/zero-ref-lemmata.component';
import { ManageLemmataComponent } from './tools/manage-lemmata/manage-lemmata.component';
import { AddLemmaComponent } from './tools/manage-lemmata/add-lemma.component';
import { EditLemmaComponent } from './tools/manage-lemmata/edit-lemma.component';
import { ManageAliasesComponent } from './tools/manage-aliases/manage-aliases.component';
import { AddAliasComponent } from './tools/manage-aliases/add-alias.component';
import { EditAliasComponent } from './tools/manage-aliases/edit-alias.component';
import { ManageCompoundPartsComponent } from './tools/manage-compound-parts/manage-compound-parts.component';
import { AddCompoundPartComponent } from './tools/manage-compound-parts/add-compound-part.component';
import { EditCompoundPartComponent } from './tools/manage-compound-parts/edit-compound-part.component';
import { ManageRootsComponent } from './tools/manage-roots/manage-roots.component';
import { AddRootComponent } from './tools/manage-roots/add-root.component';
import { EditRootComponent } from './tools/manage-roots/edit-root.component';
import { ManageSemanticGroupsComponent } from './tools/manage-semantic-groups/manage-semantic-groups.component';
import { AddSemanticGroupComponent } from './tools/manage-semantic-groups/add-semantic-group.component';
import { EditSemanticGroupComponent } from './tools/manage-semantic-groups/edit-semantic-group.component';
import { ManageTextComponent } from './tools/manage-text/manage-text.component';
import { EditTextComponent } from './tools/manage-text/edit-text.component';
import { EditTokenComponent } from './tools/manage-text/edit-token.component';
import { LemmaMeaningsComponent } from './tools/manage-text/edit-lemma-meanings.component';
import { RecompileTextsComponent } from './tools/manage-text/recompile-texts.component';
import { BackupsOverviewComponent } from './tools/backups/backups-overview.component';
import { ExportComponent } from './tools/export/export.component';

import { FourOhFourComponent } from './four-oh-four/four-oh-four.component';

import { UserGuard } from './auth/user.guard';
import { ContributorGuard } from './auth/contributor.guard';
import { EditorGuard } from './auth/editor.guard';
import { AdminGuard } from './auth/admin.guard';



import { LAYOUT } from './globals';

import { TITLE_FUNCTIONS } from './title-functions';

const routes: Routes = [
  { path: '', redirectTo: '/', pathMatch: 'full' },
  {
    path: '',
    component: MainComponent,
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'main',
    }
  },
  // dictionary stuff - searching, viewing entries, viewing lemma groups
  {
    path: 'search',
    component: EntrySearchComponent,
    data: {
      'bodyLayout': LAYOUT.NARROW,
      'titleFunction': 'search',
    }
  },
  {
    path: 'transcriptionGuide',
    component: BetacodeComponent,
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'betacode',
    }
  },
  {
    path: 'entry/:entryToken',
    component: EntryComponent,
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'entry',
    }
  },
  {
    path: 'rootGroup/:rootGroup',
    component: RootGroupComponent,
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'root-group',
    }
  },
  {
    path: 'compound/:compound',
    component: CompoundComponent,
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'compound',
    }
  },
  {
    path: 'semanticGroup/:semGroup',
    component: SemanticGroupComponent,
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'semantic-group',
    }
  },
  // browse list of entries
  {
    path: 'wordList',
    component: BrowseComponent,
    data: {
      'bodyLayout': LAYOUT.FULL,
      'titleFunction': 'word-list',
    }
  },
  {
    path: 'wordList/:firstLetter',
    component: BrowseComponent,
    data: {
      'bodyLayout': LAYOUT.FULL,
      'titleFunction': 'word-list-1letter',
    }
  },
  {
    path: 'wordList/:firstLetter/:secondLetter',
    component: BrowseComponent,
    data: {
      'bodyLayout': LAYOUT.FULL,
      'titleFunction': 'word-list-2letter',
    }
  },
  // text
  { path: 'text', redirectTo: 'text/1.1.1', pathMatch: 'full' },
  { path: 'text/', redirectTo: 'text/1.1.1', pathMatch: 'full' },
  {
    path: 'text/:locationSpec',
    component: TextComponent,
    data: {
      'bodyLayout': LAYOUT.FULL,
      'textDepth': 3,
      'titleFunction': 'text-view',
    }
  },
  // about page
  {
    path: 'about',
    component: AboutComponent,
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'about',
    }
  },
  // user page
  {
    path: 'user',
    component: UserComponent,
    canActivate: [UserGuard],
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'user',
    }
  },
  // login
  {
    path: 'login',
    component: LoginComponent,
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'login',
    }
  },
  // access denied
  {
    path: 'accessDenied',
    component: AccessDeniedComponent,
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'access-denied',
    }
  },
  // tools
  { path: 'tools', redirectTo: 'user', pathMatch: 'full' },
  { path: 'tools/', redirectTo: 'user', pathMatch: 'full' },
  // article builder
  {
    path: 'tools/articleBuilder',
    component: DefBuilderSearchComponent,
    canActivate: [ContributorGuard],
    data: {
      'bodyLayout': LAYOUT.NARROW,
      'titleFunction': 'article-builder',
    }
  },
  {
    path: 'tools/articleBuilder/:lemma',
    component: DefBuilderComponent,
    canActivate: [ContributorGuard],
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'article-builder-lemma',
    }
  },
  { path: 'tools/changelog', redirectTo: 'tools/changelog/0', pathMatch: 'full' },
  { path: 'tools/changelog/', redirectTo: 'tools/changelog/0', pathMatch: 'full' },
  {
    path: 'tools/changelog/:page',
    component: ChangeLogComponent,
    canActivate: [EditorGuard],
    data: {
      'bodyLayout': LAYOUT.FULL,
      'titleFunction': 'changelog',
    }
  },
  { path: 'tools/unwrittenArticles', redirectTo: 'tools/unwrittenArticles/0', pathMatch: 'full' },
  { path: 'tools/unwrittenArticles/', redirectTo: 'tools/unwrittenArticles/0', pathMatch: 'full' },
  {
    path: 'tools/unwrittenArticles/:page',
    component: UnwrittenArticlesComponent,
    canActivate: [EditorGuard],
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'unwritten-articles',
    }
  },
  { path: 'tools/assignedArticles', redirectTo: 'tools/assignedArticles/0', pathMatch: 'full' },
  { path: 'tools/assignedArticles/', redirectTo: 'tools/assignedArticles/0', pathMatch: 'full' },
  {
    path: 'tools/assignedArticles/:page',
    component: AssignedArticlesComponent,
    canActivate: [ContributorGuard],
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'assigned-articles',
    }
  },
  { path: 'tools/submittedDrafts', redirectTo: 'tools/submittedDrafts/0', pathMatch: 'full' },
  { path: 'tools/submittedDrafts/', redirectTo: 'tools/submittedDrafts/0', pathMatch: 'full' },
  {
    path: 'tools/submittedDrafts/:page',
    component: SubmittedDraftsComponent,
    canActivate: [EditorGuard],
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'article-drafts',
    }
  },
  { path: 'tools/myDrafts', redirectTo: 'tools/myDrafts/0', pathMatch: 'full' },
  { path: 'tools/myDrafts/', redirectTo: 'tools/myDrafts/0', pathMatch: 'full' },
  {
    path: 'tools/myDrafts/:page',
    component: UserDraftsComponent,
    canActivate: [ContributorGuard],
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'user-drafts',
    }
  },
  { path: 'tools/articleDraft', redirectTo: 'tools/articleDrafts/0', pathMatch: 'full' },
  { path: 'tools/articleDraft/', redirectTo: 'tools/articleDrafts/0', pathMatch: 'full' },
  {
    path: 'tools/articleDraft/:id',
    component: ViewArticleComponent,
    canActivate: [UserGuard],
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'article',
    }
  },
  {
    path: 'tools/reviewEntries',
    component: ReviewEntriesComponent,
    canActivate: [EditorGuard],
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'review-entries',
    }
  },
  { path: 'tools/reportedIssues', redirectTo: 'tools/reportedIssues/0/0', pathMatch: 'full' },
  { path: 'tools/reportedIssues/', redirectTo: 'tools/reportedIssues/0/0', pathMatch: 'full' },
  { path: 'tools/reportedIssues/0', redirectTo: 'tools/reportedIssues/0/0', pathMatch: 'full' },
  { path: 'tools/reportedIssues/0/', redirectTo: 'tools/reportedIssues/0/0', pathMatch: 'full' },
  { path: 'tools/reportedIssues/1', redirectTo: 'tools/reportedIssues/1/0', pathMatch: 'full' },
  { path: 'tools/reportedIssues/1/', redirectTo: 'tools/reportedIssues/1/0', pathMatch: 'full' },
  {
    path: 'tools/reportedIssues/:showResolved/:page',
    component: IssuesListComponent,
    canActivate: [EditorGuard],
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'reported-issues',
    }
  },
  { path: 'tools/issue', redirectTo: 'tools/reportedIssues/0/0', pathMatch: 'full' },
  { path: 'tools/issue/', redirectTo: 'tools/reportedIssues/0/0', pathMatch: 'full' },
  {
    path: 'tools/issue/:id',
    component: ViewIssueComponent,
    canActivate: [EditorGuard],
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'issue',
    }
  },
  {
    path: 'tools/reportIssue',
    component: ReportIssuePageComponent,
    canActivate: [UserGuard],
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'report-issue',
    }
  },
  {
    path: 'tools/inconsistencies',
    component: InconsistenciesComponent,
    canActivate: [EditorGuard],
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'inconsistencies',
    }
  },
  {
    path: 'tools/inconsistencies/invalidArticleRefs',
    component: InvalidArticleRefsComponent,
    canActivate: [EditorGuard],
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'inconsistencies-invalid-article-refs',
    }
  },
  {
    path: 'tools/inconsistencies/nonexistentLemmata',
    component: NonexistentLemmataComponent,
    canActivate: [EditorGuard],
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'inconsistencies-nonexistent-lemmata',
    }
  },
  {
    path: 'tools/inconsistencies/zeroRefLemmata',
    component: ZeroRefLemmataComponent,
    canActivate: [EditorGuard],
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'inconsistencies-zero-ref-lemmata',
    }
  },
  // manage lemmata
  {
    path: 'tools/manageLemmata',
    component: ManageLemmataComponent,
    canActivate: [EditorGuard],
    data: {
      'bodyLayout': LAYOUT.NARROW,
      'titleFunction': 'manage-lemmata',
    }
  },
  {
    path: 'tools/addLemma',
    component: AddLemmaComponent,
    canActivate: [EditorGuard],
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'add-lemma',
    }
  },
  { path: 'tools/editLemma', redirectTo: 'tools/manageLemmata', pathMatch: 'full' },
  { path: 'tools/editLemma/', redirectTo: 'tools/manageLemmata', pathMatch: 'full' },
  {
    path: 'tools/editLemma/:lemma',
    component: EditLemmaComponent,
    canActivate: [EditorGuard],
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'edit-lemma',
    }
  },
  // manage aliases
  { path: 'tools/manageAliases', redirectTo: 'tools/manageAliases/0', pathMatch: 'full' },
  { path: 'tools/manageAliases/', redirectTo: 'tools/manageAliases/0', pathMatch: 'full' },
  {
    path: 'tools/manageAliases/:page',
    component: ManageAliasesComponent,
    canActivate: [EditorGuard],
    data: {
      'bodyLayout': LAYOUT.NARROW,
      'titleFunction': 'manage-aliases',
    }
  },
  {
    path: 'tools/addAlias',
    component: AddAliasComponent,
    canActivate: [EditorGuard],
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'add-alias',
    }
  },
  { path: 'tools/editAlias', redirectTo: 'tools/manageAliases/0', pathMatch: 'full' },
  { path: 'tools/editAlias/', redirectTo: 'tools/manageAliases/0', pathMatch: 'full' },
  {
    path: 'tools/editAlias/:alias',
    component: EditAliasComponent,
    canActivate: [EditorGuard],
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'edit-alias',
    }
  },
  // manage compound parts
  { path: 'tools/manageCompoundParts', redirectTo: 'tools/manageCompoundParts/0', pathMatch: 'full' },
  { path: 'tools/manageCompoundParts/', redirectTo: 'tools/manageCompoundParts/0', pathMatch: 'full' },
  {
    path: 'tools/manageCompoundParts/:page',
    component: ManageCompoundPartsComponent,
    canActivate: [EditorGuard],
    data: {
      'bodyLayout': LAYOUT.NARROW,
      'titleFunction': 'manage-compound-parts',
    }
  },
  {
    path: 'tools/addCompoundPart',
    component: AddCompoundPartComponent,
    canActivate: [EditorGuard],
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'add-compound-part',
    }
  },
  { path: 'tools/editCompoundPart', redirectTo: 'tools/manageCompoundParts/0', pathMatch: 'full' },
  { path: 'tools/editCompoundPart/', redirectTo: 'tools/manageCompoundParts/0', pathMatch: 'full' },
  {
    path: 'tools/editCompoundPart/:compound',
    component: EditCompoundPartComponent,
    canActivate: [EditorGuard],
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'edit-compound-part',
    }
  },
  // manage roots
  { path: 'tools/manageRoots', redirectTo: 'tools/manageRoots/0', pathMatch: 'full' },
  { path: 'tools/manageRoots/', redirectTo: 'tools/manageRoots/0', pathMatch: 'full' },
  {
    path: 'tools/manageRoots/:page',
    component: ManageRootsComponent,
    canActivate: [EditorGuard],
    data: {
      'bodyLayout': LAYOUT.NARROW,
      'titleFunction': 'manage-roots',
    }
  },
  {
    path: 'tools/addRoot',
    component: AddRootComponent,
    canActivate: [EditorGuard],
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'add-root',
    }
  },
  { path: 'tools/editRoot', redirectTo: 'tools/manageRoots/0', pathMatch: 'full' },
  { path: 'tools/editRoot/', redirectTo: 'tools/manageRoots/0', pathMatch: 'full' },
  {
    path: 'tools/editRoot/:rootGroup',
    component: EditRootComponent,
    canActivate: [EditorGuard],
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'edit-root',
    }
  },
  // manage semantic groups
  { path: 'tools/manageSemanticGroups', redirectTo: 'tools/manageSemanticGroups/0', pathMatch: 'full' },
  { path: 'tools/manageSemanticGroups/', redirectTo: 'tools/manageSemanticGroups/0', pathMatch: 'full' },
  {
    path: 'tools/manageSemanticGroups/:page',
    component: ManageSemanticGroupsComponent,
    canActivate: [EditorGuard],
    data: {
      'bodyLayout': LAYOUT.NARROW,
      'titleFunction': 'manage-semantic-groups',
    }
  },
  {
    path: 'tools/addSemanticGroup',
    component: AddSemanticGroupComponent,
    canActivate: [EditorGuard],
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'add-semantic-group',
    }
  },
  { path: 'tools/editSemanticGroup', redirectTo: 'tools/manageSemanticGroups/0', pathMatch: 'full' },
  { path: 'tools/editSemanticGroup/', redirectTo: 'tools/manageSemanticGroups/0', pathMatch: 'full' },
  {
    path: 'tools/editSemanticGroup/:semGroup',
    component: EditSemanticGroupComponent,
    canActivate: [EditorGuard],
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'edit-semantic-group',
    }
  },
  // manage text
  {
    path: 'tools/manageText',
    component: ManageTextComponent,
    canActivate: [EditorGuard],
    data: {
      'bodyLayout': LAYOUT.NARROW,
      'titleFunction': 'manage-text',
    }
  },
  { path: 'tools/editText', redirectTo: 'tools/manageText', pathMatch: 'full' },
  { path: 'tools/editText/', redirectTo: 'tools/manageText', pathMatch: 'full' },
  {
    path: 'tools/editText/:location',
    component: EditTextComponent,
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'edit-text',
    }
  },
  // Editing a token
  { path: 'tools/editToken', redirectTo: 'tools/manageText', pathMatch: 'full' },
  { path: 'tools/editToken/', redirectTo: 'tools/manageText', pathMatch: 'full' },
  {
    path: 'tools/editToken/:tokenIndex',
    component: EditTokenComponent,
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'edit-token',
    }
  },
  // lemma meanings
  { path: 'tools/lemmaMeanings', redirectTo: 'tools/manageText', pathMatch: 'full' },
  { path: 'tools/lemmaMeanings/', redirectTo: 'tools/manageText', pathMatch: 'full' },
  {
    path: 'tools/lemmaMeanings/:lemma',
    component: LemmaMeaningsComponent,
    canActivate: [EditorGuard],
    data: {
      'bodyLayout': LAYOUT.FULL,
      'titleFunction': 'lemma-meanings',
    }
  },
  // recompile full text
  {
    path: 'tools/recompileText',
    component: RecompileTextsComponent,
    canActivate: [EditorGuard],
    data: {
      'bodyLayout': LAYOUT.STANDARD,
      'titleFunction': 'recompile-texts',
    }
  },
  // handle backups
  { path: 'tools/backupsOverview', redirectTo: 'tools/backupsOverview/0', pathMatch: 'full' },
  { path: 'tools/backupsOverview/', redirectTo: 'tools/backupsOverview/0', pathMatch: 'full' },
  {
    path: 'tools/backupsOverview/:page',
    component: BackupsOverviewComponent,
    canActivate: [AdminGuard],
    data: {
      'bodyLayout': LAYOUT.NARROW,
      'titleFunction': 'backups-overview',
    }
  },
  // handle lexicon
  {
    path: 'tools/export',
    component: ExportComponent,
    canActivate: [AdminGuard],
    data: {
      'bodyLayout': LAYOUT.NARROW,
      'titleFunction': 'export',
    }
  },
  // 404 page
  {
    path: '**',
    component: FourOhFourComponent,
    data: {
      'bodyLayout': LAYOUT.NARROW,
      'titleFunction': '404',
    }
  }
];

@NgModule({
  imports: [RouterModule.forRoot(routes, { useHash: true })],
  exports: [RouterModule]
})
export class AppRoutingModule {
  constructor(
    private router: Router,
    private route: ActivatedRoute,
    private title: Title
  ) {
   /* adapted from https://toddmotto.com/dynamic-page-titles-angular-2-router-events */
   this.router.events
     .pipe(
       filter(event => event instanceof NavigationEnd),
       map(() => this.route),
       map(route => {
         while (route.firstChild) route = route.firstChild;
         return route;
       }),
       filter((route) => route.outlet === 'primary'),
       mergeMap((route) => route.data)
     )
     .subscribe((data) => this.updateTitle(data));
  }

  // Given data, update the title of the page
  updateTitle (data) {
    let snap = this.route.snapshot;
    while (snap.firstChild) {
      snap = snap.firstChild;
    }
    let newTitle = TITLE_FUNCTIONS[data.titleFunction](snap.paramMap);
    this.title.setTitle(newTitle);
  }
}
