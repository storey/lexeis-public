import { Injectable } from '@angular/core';

import { Observable , of } from 'rxjs';
import { catchError, map, retry, timeout } from 'rxjs/operators';
import { HttpClient, HttpParams, HttpHeaders } from '@angular/common/http';

import { ParamMap } from '@angular/router';

import { EventSourcePolyfill } from 'ng-event-source';

import { Entry, EntryError } from './entry/entry';
import { ShortEntry, ShortEntryError } from './entry/short-entry';
import { SemanticGroup, SemanticGroupError } from './semantic-group/semantic-group';
import { RootGroup, RootGroupError } from './root-group/root-group';
import { CompoundInfo, CompoundInfoError } from './compound/compound';
import { Token, TokenError, Section, SectionError } from './text/section';
import { PreppedText, PreppedTextError } from './text/prepped-text';
import { LoginInfo, LOGIN_INFO_ERROR, UserPageInfo, UserPageInfoError } from './user/login-info';
import { ArticleInfo, ArticleInfoError } from './tools/article-builder/article-info';
import { ChangeItem, ChangeList, ChangeListError, CHANGES_PER_PAGE, ChangeLogInfo, ChangeLogInfoError } from './tools/changelog/change-list';
import { UnwrittenArticleList, UnwrittenListError, UNWRITTEN_PER_PAGE, UnwrittenPageInfo, UnwrittenPageInfoError } from './tools/unwritten-articles/unwritten-articles';
import { Article, ArticleError, ArticleList, ArticleListError, ARTICLES_PER_PAGE } from './tools/article-drafts/article-drafts';
import { ReviewEntries, ReviewEntriesError } from './tools/review-entries/entries-to-review';
import { ReportedIssue, IssueError, IssuesList, IssuesListError, ISSUES_PER_PAGE } from './tools/reported-issues/issues-list';
import { Alias, AliasError, AliasList, AliasListError, ALIASES_PER_PAGE } from './tools/manage-aliases/alias-list';
import { CompoundList, CompoundListError, COMPOUNDS_PER_PAGE } from './tools/manage-compound-parts/compound-list';
import { RootList, RootListError, ROOTS_PER_PAGE } from './tools/manage-roots/root-list';
import { SemanticGroupList, SemanticGroupListError, SEMANTIC_GROUPS_PER_PAGE } from './tools/manage-semantic-groups/semantic-group-list';
import { InconsistenciesList, InconsistenciesListError } from './tools/inconsistencies/inconsistencies-list';
import { LemmaOptionsInfo, LemmaOptionsError } from './tools/info-lists';
import { ChangeReport, ChangeReportError, ProgressUpdate, ProgressUpdateError } from './tools/change-report';
import { MeaningInfo, MeaningInfoError } from './tools/manage-text/meaning-info';
import { BackupList, BackupListError, BACKUPS_PER_PAGE } from './tools/backups/backup-info';

import { ALPHA_LOWER } from './globals';
import { AlphaCombos, AlphaCombosError } from './browse/alpha-combos';
import { BACKEND_URL, LEXEIS_URL } from './lexicon-info'

@Injectable({
  providedIn: 'root'
})
export class BackendService {
  private NUM_RETRIES = 3;
  private backend_url = BACKEND_URL;
  private lexeis_url = LEXEIS_URL;

  constructor(
    private http: HttpClient
  ) { }

  // Handle a failed request
  private handleError<T> (operation = 'operation', result?: T) {
    return (error: any): Observable<T> => {
      console.error(error); // for now we log to console

      // Let the app keep running by returning an empty result.
      return of(result as T);
    };
  }

  // Given a token, find possible associated entries
  getSearchResults(token: string): Observable<ShortEntry[]> {
    const post_data = {"searchQuery": token};
    return this.http.post<ShortEntry[]>(this.backend_url + 'getSearchResults.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => {
          let res: ShortEntry[] = [];
          for (let i = 0; i < val.length; i++) {
            res.push(new ShortEntry(val[i]));
          }
          return res;
        }),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('getSearchResults', [new ShortEntryError()]))
      );
  }

  // Given a token associated with an entry, get that entry
  getEntry(token: string, groupsAsNumbers: boolean): Observable<Entry> {
    const post_data = {
      "searchQuery": token,
      "groupsAsNumbers": groupsAsNumbers,
    };
    return this.http.post<Entry>(this.backend_url + 'getEntry.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new Entry(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('getEntry', new EntryError()))
      );
  }

  // Given a token associated with an entry, get that entry
  getArticleInfo(params : ParamMap): Observable<ArticleInfo> {
    var token = params.get('lemma');

    const post_data = {"searchQuery": token};
    return this.http.post<ArticleInfo>(this.backend_url + 'getArticleInfo.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new ArticleInfo(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('getArticleInfo', new ArticleInfoError()))
      );
  }

  // Given an id, get the information about the associated semantic group
  getSemanticGroup(params : ParamMap): Observable<SemanticGroup> {
    // + converts to an integer
    var groupIndex = +params.get('semGroup');

    const post_data = {"searchQuery": groupIndex};
    return this.http.post<SemanticGroup>(this.backend_url + '/getSemanticGroup.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new SemanticGroup(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('getSemanticGroup', new SemanticGroupError()))
      );
  }

  // Given an id, get the information about the associated stem group
  getRootGroup(params : ParamMap): Observable<RootGroup> {
    var groupIndex = params.get('rootGroup');

    const post_data = {"searchQuery": groupIndex};
    return this.http.post<RootGroup>(this.backend_url + 'getRootGroup.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new RootGroup(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('getRootGroup', new RootGroupError()))
      );
  }

  // Given an id, get the information about the associated compound
  getCompoundInfo(params : ParamMap): Observable<CompoundInfo> {
    var compound = params.get('compound');

    const post_data = {"searchQuery": compound};
    return this.http.post<CompoundInfo>(this.backend_url + 'getCompoundInfo.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new CompoundInfo(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('getCompoundInfo', new CompoundInfoError()))
      );
  }

  // Given a token associated with an entry, get that entry
  getAlphaCombos(): Observable<AlphaCombos> {
    return this.http.get<AlphaCombos>('./assets/alphaCombos.json')
      .pipe(
        map(val => new AlphaCombos(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('getAlphaCombos', new AlphaCombosError()))
      );
  }

  // Given a a first and second letter, find matching lemmata
  getMatchingLemmas(firstLetter: string, secondLetter: string): Observable<ShortEntry[]> {
    let combo: string;
    if (secondLetter == ALPHA_LOWER[0]) {
      combo = firstLetter.toLowerCase();
    } else {
      combo = firstLetter.toLowerCase() + secondLetter;
    }

    const post_data = {"searchQuery": combo};
    return this.http.post<ShortEntry[]>(this.backend_url + 'getMatchingLemmas.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => {
          let res: ShortEntry[] = [];
          for (let i = 0; i < val.length; i++) {
            res.push(new ShortEntry(val[i]));
          }
          return res;
        }),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('getMatchingLemmas', [new ShortEntryError()]))
      );
  }

  // get text associated with the given location
  getPreppedText(locationSpec: string, targetLemma: string): Observable<PreppedText> {
    const post_data = {
      "sectionCode": locationSpec,
      "targetLemma": targetLemma
    };
    return this.http.post<PreppedText>(this.backend_url + 'getPreppedText.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new PreppedText(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('getPreppedText', new PreppedTextError()))
      );
  }

  // Given a location specification, get the tokens at that location
  getSection(locationSpec: string): Observable<Section[]> {
    const post_data = {
      "locationSpec": locationSpec
    };
    return this.http.post<Section[]>(this.backend_url + 'getSection.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => {
          let res: Section[] = [];
          for (let i = 0; i < val.length; i++) {
            res.push(new Section(val[i]));
          }
          return res;
        }),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('getSection', [new SectionError()]))
      );
  }

  // Given a token index, return the token
  getToken(index: number): Observable<Token> {
    const post_data = {
      "index": index
    };
    return this.http.post<Token>(this.backend_url + 'getToken.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new Token(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('getToken', new TokenError()))
      );
  }

  // get text associated with the given location
  getLogin(): Observable<LoginInfo> {
    const post_data = {
      "dataRequest": true,
    };
    return this.http.post<LoginInfo>(this.lexeis_url + 'userCheck.php',
      JSON.stringify(post_data))
      .pipe(
        retry(this.NUM_RETRIES),
        catchError(this.handleError('getLogin', LOGIN_INFO_ERROR))
      );
  }

  // Get info for the user page
  getUserPageInfo(): Observable<UserPageInfo> {
    const post_data = {
    };
    return this.http.post<UserPageInfo>(this.backend_url + 'getUserPageInfo.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new UserPageInfo(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('getUserPageInfo', new UserPageInfoError()))
      );
  }

  // Get a page of the change log
  getChangelogInfo(page: number, userID: number, changeTypeID: number): Observable<ChangeList> {
    const post_data = {
      "page": page,
      "perPage": CHANGES_PER_PAGE,
      "userID": userID,
      "changeTypeID": changeTypeID,
    };
    return this.http.post<ChangeList>(this.backend_url + 'getChangelog.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new ChangeList(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('getChangelog', new ChangeListError()))
      );
  }

  // Get info for page of a change log
  getChangelogPageInfo(): Observable<ChangeLogInfo> {
    const post_data = {
    };
    return this.http.post<ChangeLogInfo>(this.backend_url + 'getChangelogPageInfo.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new ChangeLogInfo(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('getChangelogPageInfo', new ChangeLogInfoError()))
      );
  }

  // Undo a change
  undoChange(change: ChangeItem): Observable<ChangeReport> {
    const post_data = {
      "id": change.id
    };
    return this.http.post<ChangeReport>(this.backend_url + 'undoChange.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new ChangeReport(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('undoChange', new ChangeReportError()))
      );
  }

  // Submit a long definition
  // Take the raw and parsed articles, lemma id, and the article this one
  // is based on, if it is being edited
  submitArticle(keepAuthor: boolean, oldAuthor: number, wasOldDef: boolean, raw: string, parsed: string, lemmaid: number, editedID: number, customAuthor: string): Observable<ChangeReport> {
    const post_data = {
      "keepAuthor": keepAuthor,
      "oldAuthorID": oldAuthor,
      "wasOldDef": wasOldDef,
      "longDefRaw": raw,
      "longDef": parsed,
      "lemmaid": lemmaid,
      "predecessor": editedID,
      "customAuthor": customAuthor,
    };
    return this.http.post<ChangeReport>(this.backend_url + 'addArticle.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new ChangeReport(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('submitArticle', new ChangeReportError()))
      );
  }

  // Get a page of article drafts
  getArticleDrafts(page: number, userArticlesOnly: boolean): Observable<ArticleList> {
    const post_data = {
      "page": page,
      "perPage": ARTICLES_PER_PAGE,
      "userArticlesOnly": userArticlesOnly,
    };
    return this.http.post<ArticleList>(this.backend_url + 'getArticleDrafts.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new ArticleList(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('getArticleDrafts', new ArticleListError()))
      );
  }

  // Get a list of entries that need to be proofread
  getEntriesToReview(): Observable<ReviewEntries> {
    const post_data = {};
    return this.http.post<ReviewEntries>(this.backend_url + 'getEntriesToReview.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new ReviewEntries(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('getEntriesToReview', new ReviewEntriesError()))
      );
  }

  // Get a page of unwritten articles
  getUnwrittenArticles(page: number, getAssigned: boolean, root: string, semantic: number, freq: number): Observable<UnwrittenArticleList> {
    const post_data = {
      "page": page,
      "perPage": UNWRITTEN_PER_PAGE,
      "getAssigned": getAssigned,
      "rootFilter": root,
      "semanticFilter": semantic,
      "freqFilter": freq,
    };
    return this.http.post<UnwrittenArticleList>(this.backend_url + 'getUnwrittenArticles.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new UnwrittenArticleList(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('getUnwrittenArticles', new UnwrittenListError()))
      );
  }

  // Get info for page of unwritten articles
  getUnwrittenPageInfo(): Observable<UnwrittenPageInfo> {
    const post_data = {
    };
    return this.http.post<UnwrittenPageInfo>(this.backend_url + 'getUnwrittenPageInfo.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new UnwrittenPageInfo(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('getUnwrittenPageInfo', new UnwrittenPageInfoError()))
      );
  }

  // Get a specific article draft
  assignUnwrittenArticles(assigneeID: number, articles: number[]): Observable<ChangeReport> {
    const post_data = {
      "id": assigneeID,
      "articles": JSON.stringify(articles)
    };
    return this.http.post<ChangeReport>(this.backend_url + 'assignUnwrittenArticles.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new ChangeReport(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('assignUnwrittenArticles', new ChangeReportError()))
      );
  }

  // Get a page of articles assigned to the user
  getAssignedArticles(page: number): Observable<UnwrittenArticleList> {
    const post_data = {
      "page": page,
      "perPage": UNWRITTEN_PER_PAGE
    };
    return this.http.post<UnwrittenArticleList>(this.backend_url + 'getAssignedArticles.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new UnwrittenArticleList(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('getAssignedArticles', new UnwrittenListError()))
      );
  }

  // Get a specific article draft
  getArticle(articleID: number): Observable<Article> {
    const post_data = {
      "id": articleID,
    };
    return this.http.post<Article>(this.backend_url + 'getArticleDraft.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new Article(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('getArticle', new ArticleError()))
      );
  }

  // Accept/Delete an article draft
  resolveArticle(articleID: number, accepted: boolean): Observable<ChangeReport> {
    const post_data = {
      "id": articleID,
      "accepted": accepted
    };
    return this.http.post<ChangeReport>(this.backend_url + 'resolveArticleDraft.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new ChangeReport(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('getIssue', new ChangeReportError()))
      );
  }

  // Get a page of reported issues
  getReportedIssues(page: number, show_resolved: boolean): Observable<IssuesList> {
    const post_data = {
      "showResolved": show_resolved,
      "page": page,
      "perPage": ISSUES_PER_PAGE
    };
    return this.http.post<IssuesList>(this.backend_url + 'getIssueslist.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new IssuesList(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('getReportedIssues', new IssuesListError()))
      );
  }

  // Get a specific issue
  getIssue(issueID: number): Observable<ReportedIssue> {
    const post_data = {
      "id": issueID,
    };
    return this.http.post<ReportedIssue>(this.backend_url + 'getIssue.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new ReportedIssue(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('getIssue', new IssueError()))
      );
  }

  // Report an issue
  reportIssue(email: string, location: string, comment: string): Observable<ChangeReport> {
    const post_data = {
      "email": email,
      "location": location,
      "comment": comment,
    };
    return this.http.post<ChangeReport>(this.backend_url + 'reportIssue.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new ChangeReport(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('reportIssue', new ChangeReportError()))
      );
  }

  // Resolve an open issue
  resolveIssue(id: number, comment: string): Observable<ChangeReport> {
    const post_data = {
      "id": id,
      "comment": comment,
    };
    return this.http.post<ChangeReport>(this.backend_url + 'resolveIssue.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new ChangeReport(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('resolveIssue', new ChangeReportError()))
      );
  }

  // Get a list of inconsistencies
  getInconsistencies(incType: string): Observable<InconsistenciesList> {
    const post_data = {
      "type": incType
    };
    return this.http.post<InconsistenciesList>(this.backend_url + 'getInconsistencies.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new InconsistenciesList(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('getInconsistencies', new InconsistenciesListError()))
      );
  }

  // Add a new lemma
  addLemma(formData: any): Observable<ChangeReport> {
    let params = new HttpParams();
    let headers = new HttpHeaders();
    headers.append('enctype', 'multipart/form-data');

    const options = {
      headers: headers,
      params: params,
      reportProgress: true,
    };

    return this.http.post<ChangeReport>(this.backend_url + 'addLemma.php',
      formData, options)
      .pipe(
        map(val => new ChangeReport(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('newLemma', new ChangeReportError()))
      );
  }

  // Edit a lemma
  editLemma(formData: any): Observable<ChangeReport> {
    let params = new HttpParams();
    let headers = new HttpHeaders();
    headers.append('enctype', 'multipart/form-data');

    const options = {
      headers: headers,
      params: params,
      reportProgress: true,
    };

    return this.http.post<ChangeReport>(this.backend_url + 'editLemma.php',
      formData, options)
      .pipe(
        map(val => new ChangeReport(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('editLemma', new ChangeReportError()))
      );
  }

  // Delete a lemma
  deleteLemma(lemma: string, lemmaid: number): Observable<ChangeReport> {
    const post_data = {
      "lemma": lemma,
      "lemmaid": lemmaid
    };
    return this.http.post<ChangeReport>(this.backend_url + 'deleteLemma.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new ChangeReport(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('deleteLemma', new ChangeReportError()))
      );
  }

  // Update the status of a lemma
  updateLemmaStatus(lemma: string, oldStatus: number): Observable<ChangeReport> {
    const post_data = {
      "lemma": lemma,
      "oldStatus": oldStatus,
    };
    return this.http.post<ChangeReport>(this.backend_url + 'editLemmaStatus.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new ChangeReport(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('updateLemmaStatus', new ChangeReportError()))
      );
  }

  // Get info for editing lemmas
  getLemmaOptions(): Observable<LemmaOptionsInfo> {
    const post_data = {
    };
    return this.http.post<LemmaOptionsInfo>(this.backend_url + 'getLemmaOptionsInfo.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new LemmaOptionsInfo(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('getLemmaOptions', new LemmaOptionsError()))
      );
  }

  // Add a new alias
  addAlias(formData: any): Observable<ChangeReport> {
    let params = new HttpParams();
    let headers = new HttpHeaders();
    headers.append('enctype', 'multipart/form-data');

    const options = {
      headers: headers,
      params: params,
      reportProgress: true,
    };

    return this.http.post<ChangeReport>(this.backend_url + 'addAlias.php',
      formData, options)
      .pipe(
        map(val => new ChangeReport(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('newLemma', new ChangeReportError()))
      );
  }

  // Edit an alias
  editAlias(formData: any): Observable<ChangeReport> {
    let params = new HttpParams();
    let headers = new HttpHeaders();
    headers.append('enctype', 'multipart/form-data');

    const options = {
      headers: headers,
      params: params,
      reportProgress: true,
    };

    return this.http.post<ChangeReport>(this.backend_url + 'editAlias.php',
      formData, options)
      .pipe(
        map(val => new ChangeReport(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('editLemma', new ChangeReportError()))
      );
  }

  // Delete an alias
  deleteAlias(alias: string, aliasid: number): Observable<ChangeReport> {
    const post_data = {
      "alias": alias,
      "aliasid": aliasid
    };
    return this.http.post<ChangeReport>(this.backend_url + 'deleteAlias.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new ChangeReport(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('deleteLemma', new ChangeReportError()))
      );
  }

  // Get a page of aliases
  getAlias(alias: string): Observable<Alias> {
    const post_data = {
      "alias": alias,
    };
    return this.http.post<Alias>(this.backend_url + 'getAlias.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new Alias(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('getAliasList', new AliasError()))
      );
  }

  // Get a page of aliases
  getAliasList(page: number): Observable<AliasList> {
    const post_data = {
      "page": page,
      "perPage": ALIASES_PER_PAGE
    };
    return this.http.post<AliasList>(this.backend_url + 'getAliasList.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new AliasList(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('getAliasList', new AliasListError()))
      );
  }

  // Get a page of compound parts
  getCompoundsList(page: number): Observable<CompoundList> {
    const post_data = {
      "page": page,
      "perPage": COMPOUNDS_PER_PAGE
    };
    return this.http.post<CompoundList>(this.backend_url + 'getCompoundsList.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new CompoundList(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('getCompoundsList', new CompoundListError()))
      );
  }

  // Add a new compound
  addCompound(formData: any): Observable<ChangeReport> {
    let params = new HttpParams();
    let headers = new HttpHeaders();
    headers.append('enctype', 'multipart/form-data');

    const options = {
      headers: headers,
      params: params,
      reportProgress: true,
    };

    return this.http.post<ChangeReport>(this.backend_url + 'addCompound.php',
      formData, options)
      .pipe(
        map(val => new ChangeReport(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('addCompound', new ChangeReportError()))
      );
  }

  // Edit a compound
  editCompound(formData: any): Observable<ChangeReport> {
    let params = new HttpParams();
    let headers = new HttpHeaders();
    headers.append('enctype', 'multipart/form-data');

    const options = {
      headers: headers,
      params: params,
      reportProgress: true,
    };

    return this.http.post<ChangeReport>(this.backend_url + 'editCompound.php',
      formData, options)
      .pipe(
        map(val => new ChangeReport(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('editCompound', new ChangeReportError()))
      );
  }

  // Delete a compound
  deleteCompound(index: number, compound: string): Observable<ChangeReport> {
    const post_data = {
      "index": index,
      "item": compound
    };
    return this.http.post<ChangeReport>(this.backend_url + 'deleteCompound.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new ChangeReport(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('deleteCompound', new ChangeReportError()))
      );
  }


  // Get a page of roots
  getRootsList(page: number): Observable<RootList> {
    const post_data = {
      "page": page,
      "perPage": ROOTS_PER_PAGE
    };
    return this.http.post<CompoundList>(this.backend_url + 'getRootsList.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new RootList(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('getRootsList', new RootListError()))
      );
  }

  // Add a new root
  addRoot(formData: any): Observable<ChangeReport> {
    let params = new HttpParams();
    let headers = new HttpHeaders();
    headers.append('enctype', 'multipart/form-data');

    const options = {
      headers: headers,
      params: params,
      reportProgress: true,
    };

    return this.http.post<ChangeReport>(this.backend_url + 'addRoot.php',
      formData, options)
      .pipe(
        map(val => new ChangeReport(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('addRoot', new ChangeReportError()))
      );
  }

  // Edit a root
  editRoot(formData: any): Observable<ChangeReport> {
    let params = new HttpParams();
    let headers = new HttpHeaders();
    headers.append('enctype', 'multipart/form-data');

    const options = {
      headers: headers,
      params: params,
      reportProgress: true,
    };

    return this.http.post<ChangeReport>(this.backend_url + 'editRoot.php',
      formData, options)
      .pipe(
        map(val => new ChangeReport(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('editRoot', new ChangeReportError()))
      );
  }

  // Delete a root
  deleteRoot(index: number, root: string): Observable<ChangeReport> {
    const post_data = {
      "index": index,
      "item": root
    };
    return this.http.post<ChangeReport>(this.backend_url + 'deleteRoot.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new ChangeReport(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('deleteRoot', new ChangeReportError()))
      );
  }


  // Get a page of semantic groups
  getSemanticGroupsList(page: number): Observable<SemanticGroupList> {
    const post_data = {
      "page": page,
      "perPage": SEMANTIC_GROUPS_PER_PAGE
    };
    return this.http.post<SemanticGroupList>(this.backend_url + 'getSemanticGroupsList.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new SemanticGroupList(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('getSemanticGroupsList', new SemanticGroupListError()))
      );
  }

  // Add a new semantic group
  addSemanticGroup(formData: any): Observable<ChangeReport> {
    let params = new HttpParams();
    let headers = new HttpHeaders();
    headers.append('enctype', 'multipart/form-data');

    const options = {
      headers: headers,
      params: params,
      reportProgress: true,
    };

    return this.http.post<ChangeReport>(this.backend_url + 'addSemanticGroup.php',
      formData, options)
      .pipe(
        map(val => new ChangeReport(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('addSemanticGroup', new ChangeReportError()))
      );
  }

  // Edit a semantic group
  editSemanticGroup(formData: any): Observable<ChangeReport> {
    let params = new HttpParams();
    let headers = new HttpHeaders();
    headers.append('enctype', 'multipart/form-data');

    const options = {
      headers: headers,
      params: params,
      reportProgress: true,
    };

    return this.http.post<ChangeReport>(this.backend_url + 'editSemanticGroup.php',
      formData, options)
      .pipe(
        map(val => new ChangeReport(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('editSemanticGroup', new ChangeReportError()))
      );
  }

  // Delete a semantic group
  deleteSemanticGroup(index: number, sg: string): Observable<ChangeReport> {
    const post_data = {
      "index": index,
      "item": sg
    };
    return this.http.post<ChangeReport>(this.backend_url + 'deleteSemanticGroup.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new ChangeReport(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('deleteSemanticGroup', new ChangeReportError()))
      );
  }

  // Edit a token
  editToken(formData: any): Observable<ChangeReport> {
    let params = new HttpParams();
    let headers = new HttpHeaders();
    headers.append('enctype', 'multipart/form-data');

    const options = {
      headers: headers,
      params: params,
      reportProgress: true,
    };

    return this.http.post<ChangeReport>(this.backend_url + 'editToken.php',
      formData, options)
      .pipe(
        map(val => new ChangeReport(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('editToken', new ChangeReportError()))
      );
  }

  // Given a token associated with an entry, get that entry
  getLemmaMeanings(token: string): Observable<MeaningInfo> {
    const post_data = {"searchQuery": token};
    return this.http.post<MeaningInfo>(this.backend_url + 'getLemmaMeanings.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new MeaningInfo(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('getLemmaMeanings', new MeaningInfoError()))
      );
  }

  // Edit one or more meanings associated with a lemma
  editLemmaMeanings(formData: any): Observable<ChangeReport> {
    let params = new HttpParams();
    let headers = new HttpHeaders();
    headers.append('enctype', 'multipart/form-data');

    const options = {
      headers: headers,
      params: params,
      reportProgress: true,
    };

    return this.http.post<ChangeReport>(this.backend_url + 'editLemmaMeanings.php',
      formData, options)
      .pipe(
        map(val => new ChangeReport(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('editLemmaMeanings', new ChangeReportError()))
      );
  }

  // Recompile the prepared texts
  recompileTexts(): Observable<ProgressUpdate> {
    let url = this.backend_url + 'recompilePrepTexts.php';
    let headers = {headers: {}};

    const eventSourceObservable = Observable.create(observer => {
      const eventSource = new EventSourcePolyfill(url, headers);

      eventSource.onmessage = (x) => {
        let data = new ProgressUpdate(JSON.parse(x.data));
        if (data.complete) {
          eventSource.close();
        }
        observer.next(data);
      }

      eventSource.onerror = (e) => {
        observer.next(new ProgressUpdateError());
        eventSource.close();
      }

      return () => {
        eventSource.close();
      }
    });

    return eventSourceObservable;
  }

  // Generate Export information
  generateExport(): Observable<ProgressUpdate> {
    let url = this.backend_url + 'createLexiconExport.php';
    let headers = {headers: {}};

    const eventSourceObservable = Observable.create(observer => {
      const eventSource = new EventSourcePolyfill(url, headers);

      eventSource.onmessage = (x) => {
        let data = new ProgressUpdate(JSON.parse(x.data));
        if (data.complete) {
          eventSource.close();
        }
        observer.next(data);
      }

      eventSource.onerror = (e) => {
        observer.next(new ProgressUpdateError());
        eventSource.close();
      }

      return () => {
        eventSource.close();
      }
    });

    return eventSourceObservable;
  }

  // Create a new backup
  makeNewBackup(): Observable<ChangeReport> {
    const post_data = {};
    return this.http.post<ChangeReport>(this.backend_url + 'createNewBackup.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new ChangeReport(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('createNewBackup', new ChangeReportError()))
      );
  }

  // Get a page of the list of backups
  getBackupsList(page: number): Observable<BackupList> {
    const post_data = {
      "page": page,
      "perPage": BACKUPS_PER_PAGE
    };
    return this.http.post<BackupList>(this.backend_url + 'getBackupsList.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new BackupList(val)),
        retry(this.NUM_RETRIES),
        catchError(this.handleError('getBackupsList', new BackupListError()))
      );
  }

  // Restore from a given backup
  restoreBackup(filename: string): Observable<ChangeReport> {
    const post_data = {
      "filename": filename,
    };
    return this.http.post<ChangeReport>(this.backend_url + 'restoreBackup.php',
      JSON.stringify(post_data))
      .pipe(
        map(val => new ChangeReport(val)),
        timeout(1200*1000),
        // Do not retry
        catchError(this.handleError('restoreBackup', new ChangeReportError()))
      );
  }
}
