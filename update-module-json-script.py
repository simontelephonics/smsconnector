import xmltodict
import json
import sys
import os
import requests
import shutil
import tarfile
import hashlib

release_version = ""
release_repository_full = ""
owner = ""
token = ""

if len(sys.argv) > 1:
    release_version = sys.argv[1]
else:
    print("Empty Version!")

if len(sys.argv) > 2:
    release_repository_full = sys.argv[2]
else:
    print("Empty Repository!")

if len(sys.argv) > 3:
    owner = sys.argv[3]
else:
    print("Empty Owner!")

release_repository = release_repository_full.split("/")[-1]
release_version_clean = release_version[1:]

url_targz = "https://github.com/{}/archive/refs/tags/{}.tar.gz".format(release_repository_full, release_version)

def calc_md5(file):
    hash_md5 = hashlib.md5()
    with open(file, "rb") as f:
        for block in iter(lambda: f.read(4096), b""):
            hash_md5.update(block)
    return hash_md5.hexdigest()

print("Update:")
print(" - Reposity:", release_repository)
print(" - Version:", release_version)
print(" - File:", url_targz)


response = requests.get(url_targz, stream=True)
tar_path = "file.tar.gz"

# Save file download
with open(tar_path, "wb") as tar_file:
    shutil.copyfileobj(response.raw, tar_file)

# Extract tar.gz
with tarfile.open(tar_path, "r:gz") as tar:
    tar.extractall()

# Remove file.tar.gz
os.remove(tar_path)

# Rename folder module
name_folder_extract_org = "{}-{}".format(release_repository, release_version_clean)
name_folder_extract_fix = release_repository
os.rename(name_folder_extract_org, name_folder_extract_fix)

# Remove ".git"
git_dir = os.path.join(name_folder_extract_fix, ".github")
if os.path.exists(git_dir) and os.path.isdir(git_dir):
    shutil.rmtree(git_dir)

# Create new file tar.gz
# name_new_tar = "{}-{}.tar.gz".format(release_repository, release_version_clean)
name_new_tar = "{}.tar.gz".format(release_repository)
with tarfile.open(name_new_tar, "w:gz") as tar:
    tar.add(name_folder_extract_fix)

# Read module.xml for further analysis
module_xml = os.path.join(name_folder_extract_fix, "module.xml")
with open(module_xml, 'r') as xml_file:
    data = xmltodict.parse(xml_file.read())

# Remove folder extract
shutil.rmtree(name_folder_extract_fix)

# Read module.json
with open('module.json', 'r') as json_file:
    module = json.load(json_file)

module['changelog'] = data['module']['changelog']
module['version'] = data['module']['version']

module['location'] = "https://github.com/{}/releases/download/{}/{}".format(release_repository, release_version, name_new_tar)
module['md5sum'] = calc_md5(name_new_tar)

# Save Changes module.json
with open('module.json', 'w') as json_file:
    json.dump(module, json_file, indent=4)
