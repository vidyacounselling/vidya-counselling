"""Exercise actual remote PHP-rendered pages with disposable CI data, never production."""
import os,time,json
from pathlib import Path
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

assert os.environ.get('GITHUB_ACTIONS')=='true'
out=Path('build/evidence/browser');out.mkdir(parents=True,exist_ok=True)
options=webdriver.ChromeOptions();options.add_argument('--headless=new');options.add_argument('--no-sandbox');options.add_argument('--disable-dev-shm-usage')
driver=webdriver.Chrome(options=options);wait=WebDriverWait(driver,12);results=[]
def check(name,condition):
    assert condition,name
    results.append({'check':name,'pass':True})
def page(path,label,width=1440,height=1000):
    driver.set_window_size(width,height);driver.get('http://127.0.0.1:8000'+path)
    wait.until(lambda d:d.execute_script('return document.readyState')=='complete')
    check(label+'-renders',not ('Server Error' in driver.title or '500'==driver.title))
    check(label+'-no-document-overflow',driver.execute_script('return document.documentElement.scrollWidth<=document.documentElement.clientWidth+1'))
    driver.save_screenshot(str(out/(label+'.png')))
try:
    page('/login','login-desktop');driver.find_element(By.ID,'email').send_keys('browser@example.test');driver.find_element(By.ID,'password').send_keys('Isolated-Browser123!');driver.find_element(By.CSS_SELECTOR,'button[type=submit]').click();wait.until(EC.url_contains('/admin'))
    check('existing-login-reaches-dashboard','/admin' in driver.current_url)
    for path,label in [('/admin','dashboard'),('/admin/users','users'),('/admin/users/create','user-create'),('/admin/users/1','user-details'),('/admin/users/1/edit','user-edit'),('/admin/blogs','posts'),('/admin/blogs/create','post-create'),('/admin/blogs/1/edit','post-edit'),('/admin/blogs/1','post-details'),('/admin/comments','comments'),('/admin/taxonomy/categories','categories'),('/admin/taxonomy/tags','tags'),('/blog','public-list'),('/blog/isolated-browser-article','public-article')]:
        page(path,label+'-desktop')
    page('/admin/blogs/1/edit','editor-fidelity')
    wait.until(lambda d:d.find_elements(By.CSS_SELECTOR,'.ql-editor h2'))
    check('quill-heading-reopened',bool(driver.find_elements(By.CSS_SELECTOR,'.ql-editor h2')))
    check('quill-bold-reopened',bool(driver.find_elements(By.CSS_SELECTOR,'.ql-editor strong')))
    driver.find_element(By.ID,'body-html') # hidden textarea synchronized by real editor
    driver.find_element(By.CSS_SELECTOR,'.ql-editor').send_keys(' Autosaved private text.')
    wait.until(lambda d:'Private working copy saved' in d.find_element(By.ID,'autosave-status').text)
    check('autosave-reports-server-success',True)
    page('/blog/isolated-browser-article','public-autosave-isolation')
    check('autosave-does-not-publish','Autosaved private text.' not in driver.find_element(By.TAG_NAME,'body').text)
    page('/admin','collapsed-before');driver.find_element(By.ID,'collapse-sidebar').click();check('sidebar-collapse','sidebar-collapsed' in driver.find_element(By.TAG_NAME,'body').get_attribute('class'));driver.save_screenshot(str(out/'dashboard-collapsed.png'))
    for width,label in [(820,'tablet'),(390,'mobile')]:
        for path,name in [('/login','login'),('/admin','dashboard'),('/admin/users','users'),('/admin/users/create','user-create'),('/admin/blogs','posts'),('/admin/blogs/1/edit','post-edit'),('/admin/blogs/1','post-details'),('/admin/comments','comments'),('/blog','public-list'),('/blog/isolated-browser-article','public-article')]:
            if path=='/login':continue # signed-in guest middleware intentionally redirects
            page(path,name+'-'+label,width,1000)
    page('/admin','mobile-menu-before',390,844);driver.find_element(By.ID,'open-sidebar').click();check('mobile-menu-opens','sidebar-open' in driver.find_element(By.TAG_NAME,'body').get_attribute('class'));driver.switch_to.active_element.send_keys('\ue00c');check('escape-closes-mobile-menu','sidebar-open' not in driver.find_element(By.TAG_NAME,'body').get_attribute('class'));check('focus-returned-to-menu',driver.switch_to.active_element.get_attribute('id')=='open-sidebar')
    page('/admin/blogs/1/edit','editor-panels',1440,1000)
    driver.find_element(By.ID,'manual_schema').send_keys('{invalid');driver.find_element(By.ID,'format-schema').click();check('invalid-schema-feedback','Invalid JSON' in driver.find_element(By.ID,'schema-feedback').text)
    driver.find_element(By.ID,'manual_schema').clear()
    driver.find_element(By.CSS_SELECTOR,'form[action$="/scripts"] textarea').send_keys('{"provider":"google-analytics","measurement_id":"G-ABC123456"}')
    driver.execute_script('arguments[0].scrollIntoView()',driver.find_element(By.CSS_SELECTOR,'form[action$="/scripts"]'))
    driver.save_screenshot(str(out/'editor-script-settings.png'))

    # R3: actual rendered public and staff screens at every requested breakpoint.
    for width in [360,390,768,1280,1440]:
        for path,name in [('/','home'),('/services','services'),('/services/mental-health-counselling','service'),('/our-story','our-story'),('/reviews','reviews'),('/contact','contact'),('/blog','blog'),('/admin/services','admin-services'),('/admin/faqs/create','admin-faq-create'),('/admin/inquiries','admin-inquiries')]:
            page(path,'r3-'+name+'-'+str(width),width,1000)
    for path,name in [('/admin/services/1/edit','service-editor'),('/admin/services/1','service-schema'),('/admin/stories/create','story-create'),('/admin/reviews/create','review-create'),('/admin/faqs','faqs'),('/admin/website','website-settings')]:
        page(path,'r3-'+name,1440,1000)
    page('/contact','r3-contact-form-before',390,1000)
    from selenium.webdriver.support.ui import Select
    form=driver.find_element(By.CSS_SELECTOR,'main .inquiry-form')
    form.find_element(By.NAME,'full_name').send_keys('Isolated Browser Test')
    form.find_element(By.NAME,'email').send_keys('ui-inquiry@example.test')
    form.find_element(By.NAME,'mobile').send_keys('9000000001')
    Select(form.find_element(By.NAME,'interest')).select_by_visible_text('Mental Health')
    Select(form.find_element(By.NAME,'session_preference')).select_by_visible_text('Online')
    form.find_element(By.NAME,'consent').click();form.find_element(By.CSS_SELECTOR,'button[type=submit]').click()
    wait.until(lambda d:'has been received' in form.find_element(By.CSS_SELECTOR,'.form-result').text)
    check('r3-contact-persistence-confirmed',True)
    page('/','r3-widget-before',390,1000)
    driver.find_element(By.CSS_SELECTOR,'[data-talk-open]').click()
    dialog=driver.find_element(By.ID,'talk-dialog');check('r3-widget-modal-opens',dialog.is_displayed())
    driver.save_screenshot(str(out/'r3-widget-step-one.png'))
    Select(dialog.find_element(By.NAME,'interest')).select_by_visible_text('Marriage and Family Counseling')
    Select(dialog.find_element(By.NAME,'session_preference')).select_by_visible_text('In-person')
    dialog.find_element(By.CSS_SELECTOR,'[data-step-next]').click()
    dialog.find_element(By.NAME,'full_name').send_keys('Isolated Widget Test')
    dialog.find_element(By.NAME,'email').send_keys('ui-widget@example.test')
    dialog.find_element(By.NAME,'mobile').send_keys('9000000002')
    dialog.find_element(By.CSS_SELECTOR,'[data-step-next]').click();dialog.find_element(By.NAME,'consent').click()
    dialog.find_element(By.CSS_SELECTOR,'button[type=submit]').click()
    wait.until(lambda d:'has been received' in dialog.find_element(By.CSS_SELECTOR,'.form-result').text)
    check('r3-widget-persistence-confirmed',True)
    driver.switch_to.active_element.send_keys('\ue00c')
    check('r3-widget-escape-closes',not dialog.is_displayed())
    check('r3-widget-focus-returns',driver.switch_to.active_element.get_attribute('data-talk-open') is not None)
    page('/reviews','r3-review-before',390,1000)
    form=driver.find_element(By.CSS_SELECTOR,'main form')
    form.find_element(By.NAME,'email').send_keys('ui-review@example.test')
    form.find_element(By.NAME,'review_text').send_keys('Isolated browser moderation review, never deployed.')
    form.find_element(By.NAME,'publication_consent').click();form.find_element(By.CSS_SELECTOR,'button').click()
    wait.until(lambda d:'awaiting moderation' in form.find_element(By.CSS_SELECTOR,'.form-result').text)
    check('r3-review-persistence-pending',True)
    driver.refresh();check('r3-pending-review-not-public','Isolated browser moderation review' not in driver.find_element(By.TAG_NAME,'body').text)
    page('/admin/faqs/create','r3-faq-create-before',1440,1000)
    driver.find_element(By.NAME,'question').send_keys('An isolated browser FAQ?')
    driver.find_element(By.CSS_SELECTOR,'.ql-editor').send_keys('An isolated editable answer for browser checks.')
    Select(driver.find_element(By.NAME,'status')).select_by_value('published')
    driver.find_element(By.CSS_SELECTOR,'[data-page-search]').send_keys('Home')
    driver.find_element(By.CSS_SELECTOR,'[data-page-picker] input[type=checkbox][value="1"]').click()
    check('r3-faq-assignment-chip',bool(driver.find_elements(By.CSS_SELECTOR,'.assignment-chips button')))
    driver.find_element(By.CSS_SELECTOR,'form button.primary').click()
    wait.until(EC.url_contains('/edit'));check('r3-faq-save-reopens-editor',True)
    driver.get('http://127.0.0.1:8000/');check('r3-assigned-faq-actually-visible','An isolated browser FAQ?' in driver.find_element(By.TAG_NAME,'body').text)
    driver.save_screenshot(str(out/'r3-home-with-assigned-faq.png'))
finally:
    (out/'results.json').write_text(json.dumps(results,indent=2));driver.quit()
