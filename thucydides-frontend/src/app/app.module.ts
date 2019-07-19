import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { FormsModule} from '@angular/forms'; // NgModel lives here
import { HttpClientModule } from '@angular/common/http';  // necessary for Routing

import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { NgxPaginationModule } from 'ngx-pagination';
import { NgBusyModule } from 'ng-busy';
import {BrowserAnimationsModule} from '@angular/platform-browser/animations';


import { AppComponent } from './app.component';
import { MainComponent } from './main/main.component';
import { AboutComponent } from './about/about.component';

import { EntrySearchComponent } from './search/entry-search.component';
import { SearchComponent } from './search/search.component';
import { BetacodeComponent } from './betacode/betacode.component';
import { LemmaListComponent } from './lemma-list/lemma-list.component';
import { EntryComponent } from './entry/entry.component';
import { SemanticGroupBadgeComponent } from './entry/semantic-group-badge.component';
import { LemmaDetailComponent } from './entry/lemma-detail.component';
import { DefinitionDetailComponent } from './entry/definition-detail.component';
import { MultiDefinitionComponent } from './entry/multi-definition.component';
import { FullDefinitionComponent } from './entry/full-definition.component';
import { RootGroupComponent } from './root-group/root-group.component';
import { CompoundComponent } from './compound/compound.component';
import { SemanticGroupComponent } from './semantic-group/semantic-group.component';

import { OrganizeOccurrenceColumnsPipe, OrganizeContextColumnsPipe } from './entry/organizeOccurrenceColumns.pipe';
import { OrganizeOccurrenceInfoPipe } from './tools/article-builder/organizeOccurrenceInfo.pipe';

import { BrowseComponent } from './browse/browse.component';
import { LetterDisplayComponent } from './browse/letter-display.component';

import { TextSidebarComponent } from './text/text-sidebar.component';
import { SectionJumpComponent } from './text/section-jump.component';
import { BrowseSectionsComponent } from './text/browse-sections.component';
import { SectionDisplayComponent } from './text/section-display.component';
import { TextComponent } from './text/text.component';


import { UserComponent } from './user/user.component';
import { LoginComponent } from './login/login.component';
import { AccessDeniedComponent } from './access-denied/access-denied.component';

import { DefBuilderSearchComponent } from './tools/article-builder/def-builder-search.component';
import { DefBuilderComponent } from './tools/article-builder/def-builder.component';
import { DefEditorComponent } from './tools/article-builder/def-editor.component';
import { ChangeLogComponent } from './tools/changelog/changelog.component';
import { UnwrittenArticlesComponent } from './tools/unwritten-articles/unwritten-articles.component';
import { AssignedArticlesComponent } from './tools/unwritten-articles/assigned-articles.component';
import { ArticleDraftsListComponent } from './tools/article-drafts/article-drafts-list.component';
import { ReviewEntriesComponent } from './tools/review-entries/review-entries.component';
import { SubmittedDraftsComponent } from './tools/article-drafts/submitted-drafts.component';
import { UserDraftsComponent } from './tools/article-drafts/user-drafts.component';
import { ViewArticleComponent } from './tools/article-drafts/view-article-draft.component';
import { IssuesListComponent } from './tools/reported-issues/issues-list.component';
import { ViewIssueComponent } from './tools/reported-issues/view-issue.component';
import { ReportIssuePageComponent } from './tools/reported-issues/report-issue-page.component';
import { ReportIssueComponent } from './tools/reported-issues/report-issue.component';

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

import { InconsistenciesComponent } from './tools/inconsistencies/inconsistencies.component';
import { InconsistenciesListComponent } from './tools/inconsistencies/inconsistencies-list.component';
import { InvalidArticleRefsComponent } from './tools/inconsistencies/invalid-article-refs.component';
import { NonexistentLemmataComponent } from './tools/inconsistencies/nonexistent-lemmata.component';
import { ZeroRefLemmataComponent } from './tools/inconsistencies/zero-ref-lemmata.component';

import { FourOhFourComponent } from './four-oh-four/four-oh-four.component';
import { MiniPaginationComponent } from './tools/mini-pagination/mini-pagination.component';


import { MatchHeightDirective } from './tools/manage-text/match-height.directive';
import { InvalidReferenceDirective } from './tools/manage-text/invalid-reference.directive';


// import { httpInterceptorProviders } from './http-interceptors/index';
// import { RequestCacheWithMap } from './http-interceptors/request-cache.service';
import { BackendService } from './backend.service';
import { LoginInfoService } from './login-info.service';

import { AppRoutingModule } from './app-routing.module';

import { UserGuard } from './auth/user.guard';
import { ContributorGuard } from './auth/contributor.guard';
import { EditorGuard } from './auth/editor.guard';
import { AdminGuard } from './auth/admin.guard';



@NgModule({
  declarations: [
    AppComponent,
    MainComponent,
    AboutComponent,
    EntrySearchComponent,
    SearchComponent,
    BetacodeComponent,
    LemmaListComponent,
    EntryComponent,
    SemanticGroupBadgeComponent,
    LemmaDetailComponent,
    DefinitionDetailComponent,
    MultiDefinitionComponent,
    FullDefinitionComponent,
    RootGroupComponent,
    CompoundComponent,
    SemanticGroupComponent,
    OrganizeOccurrenceColumnsPipe,
    OrganizeContextColumnsPipe,
    OrganizeOccurrenceInfoPipe,
    BrowseComponent,
    LetterDisplayComponent,
    TextSidebarComponent,
    SectionJumpComponent,
    BrowseSectionsComponent,
    SectionDisplayComponent,
    TextComponent,
    UserComponent,
    LoginComponent,
    AccessDeniedComponent,
    DefBuilderSearchComponent,
    DefBuilderComponent,
    DefEditorComponent,
    ChangeLogComponent,
    UnwrittenArticlesComponent,
    AssignedArticlesComponent,
    ArticleDraftsListComponent,
    ReviewEntriesComponent,
    SubmittedDraftsComponent,
    UserDraftsComponent,
    ViewArticleComponent,
    IssuesListComponent,
    ViewIssueComponent,
    ReportIssuePageComponent,
    ReportIssueComponent,
    ManageLemmataComponent,
    AddLemmaComponent,
    EditLemmaComponent,
    ManageAliasesComponent,
    AddAliasComponent,
    EditAliasComponent,
    ManageCompoundPartsComponent,
    AddCompoundPartComponent,
    EditCompoundPartComponent,
    ManageRootsComponent,
    AddRootComponent,
    EditRootComponent,
    ManageSemanticGroupsComponent,
    AddSemanticGroupComponent,
    EditSemanticGroupComponent,
    ManageTextComponent,
    EditTextComponent,
    EditTokenComponent,
    LemmaMeaningsComponent,
    RecompileTextsComponent,
    BackupsOverviewComponent,
    ExportComponent,
    InconsistenciesComponent,
    InconsistenciesListComponent,
    InvalidArticleRefsComponent,
    NonexistentLemmataComponent,
    ZeroRefLemmataComponent,
    FourOhFourComponent,
    MiniPaginationComponent,
    MatchHeightDirective,
    InvalidReferenceDirective,
  ],
  imports: [
    BrowserModule,
    AppRoutingModule,
    FormsModule,
    HttpClientModule,
    AppRoutingModule,
    NgbModule.forRoot(),
    NgxPaginationModule,
    BrowserAnimationsModule,
    NgBusyModule
  ],
  providers: [
    LoginInfoService,
    BackendService,
    UserGuard,
    ContributorGuard,
    EditorGuard,
    AdminGuard,
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }
