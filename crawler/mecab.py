import pprint
import MeCab


def mecab(text):
    user_dictionary = "/usr/local/lib/mecab/dic/user_dic/user_dic.dic"
    dictionary_dir = "/usr/local/lib/mecab/dic/ipadic/"

    mecab = MeCab.Tagger("--userdic=" + user_dictionary + " --dicdir=" + dictionary_dir)

    node = mecab.parseToNode(text)

    result = []
    while node:
        result_word = {}
        pos = node.feature.split(",")
        if pos[0] != "BOS/EOS":
            result_word['str'] = node.surface + '\t' + node.feature
            result_word['text'] = node.surface
            for i in range(len(pos)):
                if pos[i] == "*":
                    pos[i] = ""
            result_word['speech'] = pos[0]
            result_word['speechInfo1'] = pos[1]
            result_word['speechInfo2'] = pos[2]
            result_word['speechInfo3'] = pos[3]
            result_word['conjugate'] = pos[4]
            result_word['conjugateType'] = pos[5]
            result_word['original'] = pos[6]
            if len(pos) == 9:
                result_word['reading'] = pos[7]
                result_word['pronaounciation'] = pos[8]
            else:
                result_word['reading'] = ""
                result_word['pronaounciation'] = ""
            speech = result_word['speech']
            if speech == "助詞" or speech == "記号" or speech == "助動詞" or speech == "その他,間投" or speech == "フィラー" or speech == "連体詞":
                pass
            else:
                result.append(result_word)
        node = node.next
    return result


if __name__ == '__main__':
    word = "すもももももももものうち"

    result = mecab(word)
    pprint.pprint(result)
